const CACHE_VERSION = 'v2';
const SHELL_CACHE = `restaurant-pos-shell-${CACHE_VERSION}`;
const RUNTIME_CACHE = `restaurant-pos-runtime-${CACHE_VERSION}`;
const DB_NAME = 'pos_offline_db';
const DB_VERSION = 1;
const SYNC_TAG = 'pos-retry-drain';

const APP_SHELL = [
    '/offline.html',
    '/manifest.webmanifest',
    '/favicon.ico',
    '/js/pos-idb.js',
    '/icons/icon-192.png',
    '/icons/icon-512.png',
    '/icons/apple-touch-icon.png',
];

self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(SHELL_CACHE)
            .then(async cache => {
                await cacheAllSettled(cache, APP_SHELL);
                try {
                    const response = await fetch('/build/manifest.json');
                    if (!response.ok) return;
                    const manifest = await response.json();
                    await cacheAllSettled(cache, resolveViteAssets(manifest));
                } catch (_) {
                    // Runtime caching will populate assets when the POS next loads online.
                }
            })
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys()
            .then(keys => Promise.all(
                keys
                    .filter(key => key.startsWith('restaurant-pos-') && ![SHELL_CACHE, RUNTIME_CACHE].includes(key))
                    .map(key => caches.delete(key))
            ))
            .then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', event => {
    const request = event.request;
    if (request.method !== 'GET') return;

    const url = new URL(request.url);
    if (url.origin !== self.location.origin) return;

    if (request.mode === 'navigate') {
        event.respondWith(networkFirstNavigation(request));
        return;
    }

    if (
        url.pathname.startsWith('/build/assets/')
        || url.pathname.startsWith('/assets/')
        || url.pathname.startsWith('/storage/')
        || url.pathname.startsWith('/icons/')
        || url.pathname === '/favicon.ico'
    ) {
        event.respondWith(cacheFirst(request));
    }
});

self.addEventListener('sync', event => {
    if (event.tag === SYNC_TAG) {
        event.waitUntil(drainQueue());
    }
});

self.addEventListener('message', event => {
    if (event.data && event.data.type === 'DRAIN_OFFLINE_QUEUE') {
        event.waitUntil(drainQueue());
    }
});

/**
 * Vite manifest `imports` are keys into the same manifest, not public URLs.
 */
function resolveViteAssets(manifest) {
    const urls = new Set();

    const visit = (keyOrEntry) => {
        const entry = typeof keyOrEntry === 'string' ? manifest[keyOrEntry] : keyOrEntry;
        if (!entry || typeof entry !== 'object') return;

        if (entry.file) {
            urls.add(entry.file.startsWith('/') ? entry.file : `/build/${entry.file}`);
        }
        (entry.css || []).forEach((file) => {
            urls.add(file.startsWith('/') ? file : `/build/${file}`);
        });
        (entry.imports || []).forEach(visit);
    };

    Object.values(manifest).forEach(visit);

    return [...urls];
}

async function cacheAllSettled(cache, urls) {
    await Promise.allSettled(
        [...new Set(urls)].map(async (url) => {
            try {
                await cache.add(url);
            } catch (_) {
                // Skip missing/optional assets so one failure does not abort install.
            }
        })
    );
}

async function networkFirstNavigation(request) {
    try {
        const response = await fetch(request);
        if (response.ok && new URL(request.url).pathname.startsWith('/seller/pos')) {
            const cache = await caches.open(RUNTIME_CACHE);
            await cache.put(request, response.clone());
        }
        return response;
    } catch (_) {
        const cached = await caches.match(request);
        return cached || caches.match('/offline.html');
    }
}

async function cacheFirst(request) {
    const cached = await caches.match(request);
    if (cached) return cached;

    const response = await fetch(request);
    if (response.ok) {
        const cache = await caches.open(RUNTIME_CACHE);
        await cache.put(request, response.clone());
    }
    return response;
}

function openDb() {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open(DB_NAME, DB_VERSION);

        request.onupgradeneeded = () => {
            const db = request.result;
            const ensure = (name, keyPath, indexes = []) => {
                if (db.objectStoreNames.contains(name)) return;
                const store = db.createObjectStore(name, { keyPath });
                indexes.forEach(([indexName, key]) => store.createIndex(indexName, key));
            };

            ensure('cache_products', 'product_id');
            ensure('cache_categories', 'category_id');
            ensure('cache_modifiers', 'modifier_id');
            ensure('cache_tables', 'table_id');
            ensure('cache_floors', 'floor_id');
            ensure('cache_customers', 'customer_id');
            ensure('cache_settings', 'seller_id');
            ensure('offline_orders', 'client_order_id', [
                ['sync_status', 'sync_status'],
                ['seller_status', ['seller_id', 'sync_status']],
            ]);
            ensure('retry_queue', 'queue_id', [
                ['state_next', ['state', 'next_attempt_at']],
                ['seller_created', ['seller_id', 'created_at']],
            ]);
            ensure('conflict_log', 'conflict_id', [
                ['resolution_status', 'resolution_status'],
            ]);
            ensure('sync_meta', 'key');
        };

        request.onsuccess = () => resolve(request.result);
        request.onerror = () => reject(request.error);
    });
}

function result(request) {
    return new Promise((resolve, reject) => {
        request.onsuccess = () => resolve(request.result);
        request.onerror = () => reject(request.error);
    });
}

function done(transaction) {
    return new Promise((resolve, reject) => {
        transaction.oncomplete = resolve;
        transaction.onerror = () => reject(transaction.error);
        transaction.onabort = () => reject(transaction.error);
    });
}

async function drainQueue() {
    const db = await openDb();
    if (!db.objectStoreNames.contains('retry_queue') || !db.objectStoreNames.contains('offline_orders')) {
        return;
    }

    const queueTx = db.transaction('retry_queue', 'readonly');
    const rows = (await result(queueTx.objectStore('retry_queue').getAll()))
        .filter(row => row.state === 'queued' && new Date(row.next_attempt_at).getTime() <= Date.now())
        .sort((a, b) => a.priority - b.priority || a.created_at.localeCompare(b.created_at));

    for (const row of rows) {
        const orderTx = db.transaction('offline_orders', 'readonly');
        const order = await result(orderTx.objectStore('offline_orders').get(row.payload_ref));
        if (!order || order.sync_status === 'synced') {
            await deleteQueue(db, row.queue_id);
            continue;
        }

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

            if (response.status === 401 || response.status === 403 || response.redirected) break;

            const body = await response.json().catch(() => ({}));
            const syncResult = body.results && body.results[0];

            if (response.ok && syncResult && syncResult.status === 'synced') {
                await patchOrder(db, order.client_order_id, {
                    sync_status: 'synced',
                    server_sale_id: syncResult.server_sale_id,
                    server_order_id: syncResult.order_id,
                    synced_at: new Date().toISOString(),
                    last_error: null,
                });
                await deleteQueue(db, row.queue_id);
                await broadcastQueueChanged();
                continue;
            }

            if (syncResult && syncResult.status === 'conflict') {
                await saveConflict(db, order, row, syncResult);
                await broadcastQueueChanged();
                continue;
            }

            throw new Error(body.message || `Sync failed (${response.status})`);
        } catch (error) {
            const attempt = row.attempt_count + 1;
            const dead = attempt >= row.max_attempts;
            await patchOrder(db, order.client_order_id, {
                sync_status: dead ? 'dead_letter' : 'pending',
                attempt_count: attempt,
                last_error: error.message,
            });
            await patchQueue(db, row.queue_id, {
                state: dead ? 'failed' : 'queued',
                attempt_count: attempt,
                next_attempt_at: new Date(Date.now() + backoff(attempt)).toISOString(),
                last_error_code: 'network_or_server',
            });
            throw error;
        }
    }
}

function backoff(attempt) {
    const schedule = [0, 5000, 30000, 120000, 600000, 1800000];
    return schedule[Math.min(attempt, schedule.length - 1)];
}

async function patchOrder(db, id, patch) {
    const tx = db.transaction('offline_orders', 'readwrite');
    const store = tx.objectStore('offline_orders');
    const current = await result(store.get(id));
    if (current) store.put({ ...current, ...patch });
    await done(tx);
}

async function patchQueue(db, id, patch) {
    const tx = db.transaction('retry_queue', 'readwrite');
    const store = tx.objectStore('retry_queue');
    const current = await result(store.get(id));
    if (current) store.put({ ...current, ...patch });
    await done(tx);
}

async function deleteQueue(db, id) {
    const tx = db.transaction('retry_queue', 'readwrite');
    tx.objectStore('retry_queue').delete(id);
    await done(tx);
}

async function saveConflict(db, order, queueRow, syncResult) {
    const tx = db.transaction(['offline_orders', 'retry_queue', 'conflict_log'], 'readwrite');
    tx.objectStore('offline_orders').put({
        ...order,
        sync_status: 'conflict',
        last_error: syncResult.message || 'Order requires review.',
    });
    tx.objectStore('retry_queue').put({ ...queueRow, state: 'conflict' });
    tx.objectStore('conflict_log').put({
        conflict_id: crypto.randomUUID(),
        client_order_id: order.client_order_id,
        queue_id: queueRow.queue_id,
        code: syncResult.code || 'sync_conflict',
        server_message: syncResult.message || 'Order requires review.',
        client_payload_snapshot: order,
        server_context_snapshot: syncResult.context || {},
        created_at: new Date().toISOString(),
        resolution_status: 'open',
    });
    await done(tx);
}

async function broadcastQueueChanged() {
    const clients = await self.clients.matchAll({ type: 'window', includeUncontrolled: true });
    clients.forEach(client => client.postMessage({ type: 'POS_OFFLINE_QUEUE_CHANGED' }));
}
