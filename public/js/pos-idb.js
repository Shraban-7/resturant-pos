(function () {
    'use strict';

    const DB_NAME = 'pos_offline_db';
    const DB_VERSION = 1;
    const SYNC_TAG = 'pos-retry-drain';
    const STORES = {
        products: 'cache_products',
        categories: 'cache_categories',
        modifiers: 'cache_modifiers',
        tables: 'cache_tables',
        floors: 'cache_floors',
        customers: 'cache_customers',
        settings: 'cache_settings',
        orders: 'offline_orders',
        queue: 'retry_queue',
        conflicts: 'conflict_log',
        meta: 'sync_meta',
    };

    let dbPromise;
    let draining = false;

    function uuid() {
        if (crypto.randomUUID) return crypto.randomUUID();
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
            const r = Math.random() * 16 | 0;
            return (c === 'x' ? r : (r & 0x3 | 0x8)).toString(16);
        });
    }

    function requestResult(request) {
        return new Promise((resolve, reject) => {
            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error);
        });
    }

    function transactionDone(transaction) {
        return new Promise((resolve, reject) => {
            transaction.oncomplete = resolve;
            transaction.onerror = () => reject(transaction.error);
            transaction.onabort = () => reject(transaction.error);
        });
    }

    function openDb() {
        if (dbPromise) return dbPromise;

        dbPromise = new Promise((resolve, reject) => {
            const request = indexedDB.open(DB_NAME, DB_VERSION);

            request.onupgradeneeded = () => {
                const db = request.result;
                createStore(db, STORES.products, 'product_id');
                createStore(db, STORES.categories, 'category_id');
                createStore(db, STORES.modifiers, 'modifier_id');
                createStore(db, STORES.tables, 'table_id');
                createStore(db, STORES.floors, 'floor_id');
                createStore(db, STORES.customers, 'customer_id');
                createStore(db, STORES.settings, 'seller_id');

                if (!db.objectStoreNames.contains(STORES.orders)) {
                    const store = db.createObjectStore(STORES.orders, { keyPath: 'client_order_id' });
                    store.createIndex('sync_status', 'sync_status');
                    store.createIndex('seller_status', ['seller_id', 'sync_status']);
                }

                if (!db.objectStoreNames.contains(STORES.queue)) {
                    const store = db.createObjectStore(STORES.queue, { keyPath: 'queue_id' });
                    store.createIndex('state_next', ['state', 'next_attempt_at']);
                    store.createIndex('seller_created', ['seller_id', 'created_at']);
                }

                if (!db.objectStoreNames.contains(STORES.conflicts)) {
                    const store = db.createObjectStore(STORES.conflicts, { keyPath: 'conflict_id' });
                    store.createIndex('resolution_status', 'resolution_status');
                }

                createStore(db, STORES.meta, 'key');
            };

            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error);
        });

        return dbPromise;
    }

    function createStore(db, name, keyPath) {
        if (!db.objectStoreNames.contains(name)) {
            db.createObjectStore(name, { keyPath });
        }
    }

    async function getMeta(key) {
        const db = await openDb();
        const tx = db.transaction(STORES.meta, 'readonly');
        return requestResult(tx.objectStore(STORES.meta).get(key));
    }

    async function setMeta(key, value) {
        const db = await openDb();
        const tx = db.transaction(STORES.meta, 'readwrite');
        tx.objectStore(STORES.meta).put({ key, value, updated_at: new Date().toISOString() });
        await transactionDone(tx);
        return value;
    }

    async function deviceId() {
        const existing = await getMeta('device_id');
        if (existing && existing.value) return existing.value;
        return setMeta('device_id', uuid());
    }

    async function replaceStore(storeName, rows) {
        const db = await openDb();
        const tx = db.transaction(storeName, 'readwrite');
        const store = tx.objectStore(storeName);
        store.clear();
        (rows || []).forEach(row => store.put(row));
        await transactionDone(tx);
    }

    async function cacheSnapshot(snapshot) {
        if (!snapshot || !snapshot.seller_id) return;

        const modifiers = [];
        (snapshot.products || []).forEach(product => {
            (product.modifiers || []).forEach(modifier => {
                modifiers.push({
                    ...modifier,
                    modifier_id: modifier.id,
                    product_id: product.product_id,
                });
            });
        });

        await Promise.all([
            replaceStore(STORES.products, snapshot.products || []),
            replaceStore(STORES.categories, snapshot.categories || []),
            replaceStore(STORES.modifiers, modifiers),
            replaceStore(STORES.tables, snapshot.tables || []),
            replaceStore(STORES.floors, snapshot.floors || []),
            replaceStore(STORES.customers, snapshot.customers || []),
            replaceStore(STORES.settings, [{
                seller_id: snapshot.seller_id,
                currency: snapshot.currency || 'BDT',
                cached_at: new Date().toISOString(),
            }]),
            setMeta('last_catalog_sync_at', new Date().toISOString()),
            setMeta('seller_id', snapshot.seller_id),
        ]);
    }

    async function queueOrder(order) {
        const db = await openDb();
        const now = new Date().toISOString();
        const clientOrderId = order.client_order_id || uuid();
        const queueId = uuid();
        const document = {
            ...order,
            client_order_id: clientOrderId,
            device_id: order.device_id || await deviceId(),
            created_at_client: order.created_at_client || now,
            sync_status: 'pending',
            attempt_count: 0,
            schema_version: 1,
        };

        const queueRecord = {
            queue_id: queueId,
            type: 'order.create',
            payload_ref: clientOrderId,
            seller_id: document.seller_id,
            device_id: document.device_id,
            priority: 10,
            created_at: now,
            next_attempt_at: now,
            attempt_count: 0,
            max_attempts: 25,
            state: 'queued',
        };

        const tx = db.transaction([STORES.orders, STORES.queue], 'readwrite');
        tx.objectStore(STORES.orders).put(document);
        tx.objectStore(STORES.queue).put(queueRecord);
        await transactionDone(tx);

        notifyChanged();
        await registerBackgroundSync();
        return document;
    }

    async function getAll(storeName) {
        const db = await openDb();
        const tx = db.transaction(storeName, 'readonly');
        return requestResult(tx.objectStore(storeName).getAll());
    }

    async function pendingCount() {
        const orders = await getAll(STORES.orders);
        return orders.filter(order => ['pending', 'syncing'].includes(order.sync_status)).length;
    }

    function backoff(attempt) {
        const schedule = [0, 5000, 30000, 120000, 600000, 1800000];
        const base = schedule[Math.min(attempt, schedule.length - 1)];
        const jitter = attempt <= 1 ? 0 : base * ((Math.random() * 0.4) - 0.2);
        return Math.max(0, base + jitter);
    }

    async function drain() {
        if (draining || !navigator.onLine) return;
        draining = true;

        try {
            const db = await openDb();
            const queue = (await getAll(STORES.queue))
                .filter(row => row.state === 'queued' && new Date(row.next_attempt_at).getTime() <= Date.now())
                .sort((a, b) => a.priority - b.priority || a.created_at.localeCompare(b.created_at));

            for (const row of queue) {
                const orderTx = db.transaction(STORES.orders, 'readonly');
                const order = await requestResult(orderTx.objectStore(STORES.orders).get(row.payload_ref));
                if (!order || order.sync_status === 'synced') {
                    await deleteQueue(row.queue_id);
                    continue;
                }

                await updateOrder(order.client_order_id, { sync_status: 'syncing' });

                try {
                    const response = await fetch('/api/seller/pos/offline-sync', {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'Idempotency-Key': order.client_order_id,
                        },
                        body: JSON.stringify({ orders: [order] }),
                    });

                    if (response.status === 401 || response.status === 403 || response.redirected) {
                        await updateOrder(order.client_order_id, {
                            sync_status: 'pending',
                            last_error: 'Authentication required before synchronization.',
                        });
                        break;
                    }

                    const body = await response.json().catch(() => ({}));
                    const result = body.results && body.results[0];

                    if (response.ok && result && result.status === 'synced') {
                        await updateOrder(order.client_order_id, {
                            sync_status: 'synced',
                            server_sale_id: result.server_sale_id,
                            server_order_id: result.order_id,
                            synced_at: new Date().toISOString(),
                            last_error: null,
                        });
                        await deleteQueue(row.queue_id);
                        continue;
                    }

                    if (result && result.status === 'conflict') {
                        await recordConflict(order, row, result);
                        continue;
                    }

                    if (response.status === 422 || response.status === 409) {
                        await recordConflict(order, row, {
                            code: 'server_rejected',
                            message: body.message || 'Server rejected the offline order.',
                            context: body.errors || {},
                        });
                        continue;
                    }

                    throw new Error(body.message || `Sync failed (${response.status})`);
                } catch (error) {
                    const attempt = row.attempt_count + 1;
                    const dead = attempt >= row.max_attempts;
                    await updateOrder(order.client_order_id, {
                        sync_status: dead ? 'dead_letter' : 'pending',
                        attempt_count: attempt,
                        last_error: error.message,
                    });
                    await updateQueue(row.queue_id, {
                        state: dead ? 'failed' : 'queued',
                        attempt_count: attempt,
                        next_attempt_at: new Date(Date.now() + backoff(attempt)).toISOString(),
                        last_error_code: 'network_or_server',
                    });
                    if (!navigator.onLine) break;
                }
            }
        } finally {
            draining = false;
            notifyChanged();
        }
    }

    async function updateOrder(id, patch) {
        const db = await openDb();
        const tx = db.transaction(STORES.orders, 'readwrite');
        const store = tx.objectStore(STORES.orders);
        const current = await requestResult(store.get(id));
        if (current) store.put({ ...current, ...patch });
        await transactionDone(tx);
    }

    async function updateQueue(id, patch) {
        const db = await openDb();
        const tx = db.transaction(STORES.queue, 'readwrite');
        const store = tx.objectStore(STORES.queue);
        const current = await requestResult(store.get(id));
        if (current) store.put({ ...current, ...patch });
        await transactionDone(tx);
    }

    async function deleteQueue(id) {
        const db = await openDb();
        const tx = db.transaction(STORES.queue, 'readwrite');
        tx.objectStore(STORES.queue).delete(id);
        await transactionDone(tx);
    }

    async function recordConflict(order, queueRow, result) {
        const db = await openDb();
        const conflict = {
            conflict_id: uuid(),
            client_order_id: order.client_order_id,
            queue_id: queueRow.queue_id,
            code: result.code || 'sync_conflict',
            server_message: result.message || 'Order requires review.',
            client_payload_snapshot: order,
            server_context_snapshot: result.context || {},
            created_at: new Date().toISOString(),
            resolution_status: 'open',
        };
        const tx = db.transaction([STORES.orders, STORES.queue, STORES.conflicts], 'readwrite');
        tx.objectStore(STORES.orders).put({
            ...order,
            sync_status: 'conflict',
            last_error: conflict.server_message,
        });
        tx.objectStore(STORES.queue).put({ ...queueRow, state: 'conflict' });
        tx.objectStore(STORES.conflicts).put(conflict);
        await transactionDone(tx);
    }

    async function registerBackgroundSync() {
        if (!('serviceWorker' in navigator)) return;
        const registration = await navigator.serviceWorker.ready;
        if ('sync' in registration) {
            try {
                await registration.sync.register(SYNC_TAG);
            } catch (_) {
                // The online/visibility fallback below will drain the same durable queue.
            }
        }
    }

    async function registerServiceWorker() {
        if (!('serviceWorker' in navigator)) return;
        try {
            await navigator.serviceWorker.register('/sw.js', { scope: '/' });
        } catch (error) {
            console.warn('Service Worker registration failed', error);
        }
    }

    function notifyChanged() {
        window.dispatchEvent(new CustomEvent('pos-offline-queue-changed'));
        if ('BroadcastChannel' in window) {
            const channel = new BroadcastChannel('pos-offline-events');
            channel.postMessage({ type: 'queue-changed' });
            channel.close();
        }
    }

    async function clearSellerData() {
        const db = await openDb();
        const names = Object.values(STORES);
        const tx = db.transaction(names, 'readwrite');
        names.forEach(name => tx.objectStore(name).clear());
        await transactionDone(tx);

        if ('caches' in window) {
            const keys = await caches.keys();
            await Promise.all(keys.filter(key => key.startsWith('restaurant-pos-')).map(key => caches.delete(key)));
        }
    }

    window.PosOffline = {
        DB_NAME,
        STORES,
        uuid,
        deviceId,
        cacheSnapshot,
        queueOrder,
        pendingCount,
        drain,
        getAll,
        clearSellerData,
    };

    window.addEventListener('online', drain);
    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible' && navigator.onLine) drain();
    });
    window.addEventListener('load', async () => {
        await registerServiceWorker();
        if (window.POS_OFFLINE_CONFIG) await cacheSnapshot(window.POS_OFFLINE_CONFIG);
        if (navigator.onLine) await drain();
    });
})();
