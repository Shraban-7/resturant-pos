@extends('layouts.pos')
@section('title', 'POS Terminal')

@section('content')

@php
    $subtotal = $totalPrice = 0;
    $categoryIcons = [
        'Set Menu' => 'ri-restaurant-2-line',
        'Appetizer' => 'ri-restaurant-line',
        'Pasta' => 'ri-restaurant-line',
        'Soft Drinks' => 'ri-goblet-2-line',
        'Main Course' => 'ri-bowl-line',
        'Desserts' => 'ri-cake-2-line',
        'Seafood' => 'ri-bowl-line',
        'Salads' => 'ri-leaf-line',
        'Soups' => 'ri-bowl-line',
        'BBQ' => 'ri-fire-line',
    ];
@endphp

<x-error-modal />

<div class="flex flex-col h-screen bg-slate-50 overflow-hidden" x-data="posApp()" x-cloak>
    <div id="offlineStatusBanner" class="hidden shrink-0 px-4 py-2 text-sm font-medium bg-amber-100 text-amber-900 border-b border-amber-200">
        <div class="flex items-center justify-between gap-3">
            <span><i class="ri-wifi-off-line mr-1"></i> Offline mode — completed orders will sync automatically.</span>
            <span id="offlinePendingCount" class="badge badge-warning hidden"></span>
        </div>
    </div>

    {{-- =================== TOP BAR =================== --}}
    <header class="bg-white border-b border-slate-200 h-16 flex items-center gap-3 px-4 shrink-0 z-20">
        <a href="{{ route('seller.dashboard') }}" class="flex items-center gap-2.5 shrink-0">
            <span class="flex items-center justify-center h-9 w-9 rounded-lg bg-brand-600 text-white">
                <i class="ri-restaurant-2-line text-lg"></i>
            </span>
            <div class="hidden sm:block">
                <div class="text-sm font-semibold text-slate-900 leading-tight">POS Terminal</div>
                <div class="text-[10px] text-slate-500 uppercase tracking-wider">{{ auth()->user()->name }}</div>
            </div>
        </a>

        <div class="flex-1 max-w-xl">
            <div class="relative">
                <i class="ri-search-line absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"></i>
                <input id="productNameSearch" type="text" class="form-control pl-10" placeholder="Search products by name...">
            </div>
        </div>

        <div class="flex items-center gap-1">
            <a href="{{ route('seller.kds.index') }}"
               class="relative btn btn-ghost btn-icon"
               title="Kitchen Display"
               id="posKitchenBadgeLink">
                <i class="ri-tablet-line text-lg"></i>
                <span id="posKitchenReadyBadge"
                      class="absolute -top-0.5 -right-0.5 min-w-[1.1rem] h-[1.1rem] px-1 rounded-full bg-emerald-500 text-white text-[10px] font-bold leading-[1.1rem] text-center hidden">0</span>
            </a>
            <button type="button" id="offlineSyncButton"
                    class="relative btn btn-ghost btn-icon hidden"
                    title="Synchronize offline orders">
                <i class="ri-cloud-line text-lg"></i>
                <span id="offlineSyncBadge"
                      class="absolute -top-0.5 -right-0.5 min-w-[1.1rem] h-[1.1rem] px-1 rounded-full bg-amber-500 text-white text-[10px] font-bold leading-[1.1rem] text-center">0</span>
            </button>
            <button type="button" id="productCodeBtn" class="btn btn-ghost btn-icon" title="Scan barcode" @click="barcodeOpen = true">
                <i class="ri-barcode-line text-lg"></i>
            </button>
            <button type="button" id="fullscreen-btn" class="btn btn-ghost btn-icon" title="Fullscreen">
                <i class="ri-fullscreen-line text-lg"></i>
            </button>
            <button type="button" id="refresh-btn" class="btn btn-ghost btn-icon" title="Refresh">
                <i class="ri-loop-right-line text-lg"></i>
            </button>
            <a href="{{ route('seller.dashboard') }}" class="btn btn-ghost btn-icon" title="Dashboard">
                <i class="ri-dashboard-line text-lg"></i>
            </a>
            <a href="{{ route('logout') }}" class="btn btn-ghost btn-icon" title="Logout">
                <i class="ri-logout-box-r-line text-lg"></i>
            </a>
        </div>
    </header>

    {{-- =================== MAIN SPLIT =================== --}}
    <div class="flex-1 flex overflow-hidden">

        {{-- ===== LEFT: PRODUCTS ===== --}}
        <main class="flex-1 overflow-y-auto p-4 pb-24 lg:pb-4">

            {{-- Recent Orders --}}
            @include('components.pos._order-chips', ['title' => 'Recent Orders', 'icon' => 'ri-receipt-2-line', 'sales' => $recentSales, 'routeName' => 'seller.sales.invoice'])

            {{-- Running Orders --}}
            @include('components.pos._order-chips', ['title' => 'Running Orders', 'icon' => 'ri-restart-line', 'sales' => $runningSales, 'routeName' => 'seller.pos.index', 'showTable' => true])

            {{-- Dining Tables --}}
            <div class="mb-5">
                <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Dining Tables</h3>
                <div class="flex flex-wrap gap-2">
                    @foreach ($diningTables as $table)
                        <x-seller.dining-table-card :table="$table" />
                    @endforeach
                </div>
            </div>

            {{-- Categories --}}
            <div class="mb-5">
                <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Categories</h3>
                <div class="flex gap-3 overflow-x-auto pb-1 -mx-1 px-1" id="categoryScroll" style="scrollbar-width: none;">
                    <button class="category-card shrink-0 active" data-category="all" onclick="window.filterCategory('all', this)" type="button">
                        <i class="ri-apps-2-line"></i>
                        <span class="category-name">All</span>
                    </button>
                    @foreach ($categories as $category)
                        @php $icon = $categoryIcons[$category->name] ?? 'ri-folder-line'; @endphp
                        <button class="category-card shrink-0" data-category="{{ $category->id }}" onclick="window.filterCategory({{ $category->id }}, this)" type="button">
                            <i class="{{ $icon }}"></i>
                            <span class="category-name">{{ $category->name }}</span>
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Products --}}
            <div>
                <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Menu</h3>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-5 gap-3" id="productsGrid">
                    @foreach ($products as $product)
                        <x-pos.item :item="$product" />
                    @endforeach
                </div>
            </div>
        </main>

        {{-- ===== RIGHT: CART (DESKTOP) ===== --}}
        <aside class="hidden lg:flex w-[400px] xl:w-[440px] bg-white border-l border-slate-200 flex-col shrink-0">
            @include('components.pos._cart-panel', [
                'subtotal' => $subtotal,
                'totalPrice' => $totalPrice,
                'customers' => $customers,
                'diningTables' => $diningTables,
                'employees' => $employees,
                'cart' => $cart,
                'sale' => $sale ?? null,
                'saleItems' => $saleItems ?? [],
                'isMobile' => false,
            ])
        </aside>
    </div>

    {{-- =================== MOBILE FLOATING CART BUTTON =================== --}}
    <button class="lg:hidden fixed bottom-4 left-4 right-4 z-30 btn btn-primary shadow-lg rounded-full h-14 text-base"
            @click="cartOpen = true">
        <i class="ri-shopping-cart-2-line text-xl"></i>
        <span>View Cart</span>
        <span class="ml-auto px-2 py-0.5 rounded bg-white/20 text-xs font-semibold" id="mobileCartCount">0</span>
    </button>

    {{-- =================== MOBILE CART DRAWER =================== --}}
    <div x-show="cartOpen" x-transition.opacity class="lg:hidden fixed inset-0 z-40" style="display:none" @keydown.escape.window="cartOpen = false">
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" @click="cartOpen = false"></div>
        <aside class="absolute bottom-0 left-0 right-0 bg-white rounded-t-2xl max-h-[90vh] flex flex-col shadow-2xl"
               x-transition:enter="transition transform ease-out duration-300"
               x-transition:enter-start="translate-y-full"
               x-transition:enter-end="translate-y-0"
               x-transition:leave="transition transform ease-in duration-200"
               x-transition:leave-start="translate-y-0"
               x-transition:leave-end="translate-y-full">
            <div class="flex items-center justify-between p-4 border-b border-slate-200">
                <h2 class="text-base font-semibold text-slate-900">Current Order</h2>
                <button type="button" class="btn btn-ghost btn-icon" @click="cartOpen = false">
                    <i class="ri-close-line text-xl"></i>
                </button>
            </div>
            <div class="flex-1 overflow-y-auto">
                @include('components.pos._cart-panel', [
                    'subtotal' => $subtotal,
                    'totalPrice' => $totalPrice,
                    'customers' => $customers,
                    'diningTables' => $diningTables,
                    'employees' => $employees,
                    'cart' => $cart,
                    'sale' => $sale ?? null,
                    'saleItems' => $saleItems ?? [],
                    'isMobile' => true,
                ])
            </div>
        </aside>
    </div>

    {{-- =================== BARCODE MODAL =================== --}}
    <div x-show="barcodeOpen" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none" @keydown.escape.window="barcodeOpen = false">
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" @click="barcodeOpen = false"></div>
        <div class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl border border-slate-200 p-6" @click.stop>
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-semibold text-slate-900">Scan Barcode</h3>
                <button type="button" class="btn btn-ghost btn-icon" @click="barcodeOpen = false">
                    <i class="ri-close-line text-xl"></i>
                </button>
            </div>
            <div class="relative">
                <i class="ri-barcode-line absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input type="text" id="barcodeInput" class="form-control pl-10 text-lg" placeholder="Scan or type code..." @keyup.enter="handleBarcode($event.target.value)">
            </div>
            <p class="mt-2 text-xs text-slate-500">Press Enter to add the matching product to cart.</p>
        </div>
    </div>

    {{-- Item Modal (Alpine) --}}
    <x-pos.item-modal />

    {{-- Recent Sales Modal --}}
    <x-pos.recent-sales-modal :sales="$recentSales" />

</div>

@push('footer')
<script>
    window.POS_OFFLINE_CONFIG = {
        seller_id: {{ (int) auth()->id() }},
        currency: 'BDT',
        products: @json($products->map(fn ($product) => [
            'product_id' => $product->id,
            'name' => $product->name,
            'selling_price' => (float) $product->selling_price,
            'buying_price' => (float) $product->buying_price,
            'available_stock' => (float) $product->availableStock,
            'category_id' => $product->product_category_id ?? $product->category_id,
            'unit' => $product->unit?->short_name,
            'image' => $product->image,
            'active' => (bool) $product->is_active,
            'modifiers' => $productModifiersMap[$product->id] ?? [],
        ])->values()),
        categories: @json($categories->map(fn ($category) => [
            'category_id' => $category->id,
            'name' => $category->name,
        ])->values()),
        tables: @json($diningTables->map(fn ($table) => [
            'table_id' => $table->id,
            'name' => $table->name,
            'status' => $table->status,
            'floor_id' => $table->floor_id,
        ])->values()),
        floors: @json($diningTables->pluck('floor')->filter()->unique('id')->map(fn ($floor) => [
            'floor_id' => $floor->id,
            'name' => $floor->name,
        ])->values()),
        customers: @json($customers->take(100)->map(fn ($customer) => [
            'customer_id' => $customer->id,
            'name' => $customer->name,
            'phone' => $customer->phone,
        ])->values()),
    };

    function posApp() {
        return {
            cartOpen: false,
            barcodeOpen: false,
        };
    }

    document.addEventListener('DOMContentLoaded', function () {
        const csrf = document.querySelector('meta[name="csrf-token"]').content;
        const orderId = "{{ $cart->order_id }}";
        const isSale = {{ request('sale') ? 'true' : 'false' }};
        const saleOrderId = "{{ $sale->order_id ?? '' }}";

        const $cart = document.getElementById('cart');
        const $subtotal = document.getElementById('subtotal');
        const $totalPrice = document.getElementById('totalPrice');
        const $due = document.getElementById('due');
        const $discountInput = document.getElementById('discountInput');
        const $paidInput = document.getElementById('paidInput');
        const $customerSelect = document.getElementById('customerSelect');
        const $tableSelect = document.getElementById('tableSelect');
        const $employeeSelect = document.getElementById('employeeSelect');
        const $customerName = document.getElementById('customer_name');
        const $customerPhone = document.getElementById('customer_phone');
        const $note = document.getElementById('note');
        const $mobileCartCount = document.getElementById('mobileCartCount');

        const $itemModalEl = document.getElementById('itemModal');
        const $itemModal = $itemModalEl ? $itemModalEl.closest('[x-data]') : null;

        function showError(msg) {
            window.dispatchEvent(new CustomEvent('open-error', { detail: msg }));
        }

        function parseJsonAttribute(value, fallback = []) {
            if (!value) return fallback;
            try { return JSON.parse(value); } catch (_) { return fallback; }
        }

        function offlineCartItems() {
            return Array.from(document.querySelectorAll('#cart .cart-item')).map(el => ({
                product_id: Number(el.dataset.itemid),
                quantity: Number(el.querySelector('.quantityInput')?.value || 0),
                unit_price_snapshot: Number(el.dataset.unitPrice || 0),
                discount: Number(el.dataset.discount || 0),
                modifiers: parseJsonAttribute(el.dataset.modifiers),
                notes: el.dataset.note || null,
            })).filter(item => item.product_id && item.quantity > 0);
        }

        function makeOfflineOrder(clientOrderId, deviceId) {
            const subtotal = offlineCartItems().reduce(
                (sum, item) => sum + Math.max(0, item.unit_price_snapshot * item.quantity - item.discount),
                0
            );
            const discount = Number($discountInput.value || 0);
            const payable = Math.max(0, subtotal - discount);
            const paid = Number($paidInput.value || 0);
            const tableId = Number($tableSelect?.value || 0) || null;

            return {
                client_order_id: clientOrderId,
                device_id: deviceId,
                seller_id: {{ (int) auth()->id() }},
                source_order_id: orderId,
                channel: tableId ? 'dine_in' : 'retail',
                dining_table_id: tableId,
                customer_id: Number($customerSelect?.value || 0) || null,
                customer_name: $customerName?.value || null,
                customer_phone: $customerPhone?.value || null,
                seller_employee_id: Number($employeeSelect?.value || 0) || null,
                items: offlineCartItems(),
                amounts: {
                    subtotal,
                    discount,
                    payable,
                    paid,
                    due: payable - paid,
                    payment_type: 'cash',
                },
                note: $note.value || null,
                created_at_client: new Date().toISOString(),
                schema_version: 1,
            };
        }

        function offlineCartElement(line) {
            const temporaryId = `offline-${window.PosOffline.uuid()}`;
            const element = document.createElement('div');
            element.className = 'cart-item bg-white border border-amber-300 rounded-lg p-2.5 flex items-center gap-2.5';
            element.id = `cart-item-${temporaryId}`;
            Object.assign(element.dataset, {
                id: temporaryId,
                itemid: String(line.productId),
                name: line.name,
                unitPrice: String(line.unitPrice),
                discount: String(line.discount),
                note: line.note || '',
                modifiers: JSON.stringify(line.modifiers || []),
                source: 'offline',
            });

            const details = document.createElement('div');
            details.className = 'flex-1 min-w-0';
            const name = document.createElement('div');
            name.className = 'text-sm font-medium text-slate-800 truncate';
            name.textContent = line.name;
            details.appendChild(name);
            if (line.modifiers?.length) {
                const modifiers = document.createElement('div');
                modifiers.className = 'text-[10px] text-slate-500 truncate';
                modifiers.textContent = line.modifiers.map(item => item.name).join(', ');
                details.appendChild(modifiers);
            }
            const controls = document.createElement('div');
            controls.className = 'flex items-center gap-1 mt-1';
            controls.innerHTML = `<button type="button" class="qty-btn decrement"><i class="ri-subtract-line text-sm pointer-events-none"></i></button>
                <input type="text" class="quantityInput qty-input" value="${line.quantity}" readonly>
                <button type="button" class="qty-btn increment"><i class="ri-add-line text-sm pointer-events-none"></i></button>`;
            details.appendChild(controls);

            const side = document.createElement('div');
            side.className = 'text-right shrink-0';
            const price = document.createElement('div');
            price.className = 'text-sm font-semibold text-slate-900 price';
            price.textContent = String((line.unitPrice * line.quantity) - line.discount);
            const remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'text-xs text-red-600 hover:underline mt-0.5';
            remove.textContent = 'Remove';
            remove.addEventListener('click', () => {
                element.remove();
                updateCartTotals();
            });
            side.append(price, remove);
            element.append(details, side);
            return element;
        }

        function updateOfflineLine(cartItem) {
            const quantity = Number(cartItem.querySelector('.quantityInput')?.value || 0);
            const total = Math.max(0, Number(cartItem.dataset.unitPrice || 0) * quantity - Number(cartItem.dataset.discount || 0));
            const price = cartItem.querySelector('.price');
            if (price) price.textContent = total.toFixed(2);
            updateCartTotals();
        }

        function updateCartTotals() {
            let total = 0;
            let count = 0;
            document.querySelectorAll('#cart .cart-item, #cart .sale-item').forEach(el => {
                const priceEl = el.querySelector('.price');
                if (priceEl) total += parseFloat(priceEl.textContent) || 0;
                const qtyEl = el.querySelector('.quantityInput, .saleQuantityInput');
                if (qtyEl) count += parseInt(qtyEl.value) || 0;
            });
            if ($subtotal) $subtotal.textContent = total;
            if ($totalPrice) $totalPrice.textContent = total;
            if ($mobileCartCount) $mobileCartCount.textContent = count;
            updateCheckoutPrice();
        }

        function updateCheckoutPrice() {
            let subtotal = parseFloat($subtotal.textContent) || 0;
            let total = subtotal;
            let discount = parseFloat($discountInput.value) || 0;
            let paid = parseFloat($paidInput.value) || 0;
            let due = total;
            if (discount) total = subtotal - discount;
            if (paid) due = (total - paid);
            $totalPrice.textContent = total;
            $due.textContent = due;
        }

        function setItemToCart(item, cartHtml) {
            const itemEl = document.getElementById('item-' + item.id);
            if (itemEl) {
                const stockEl = itemEl.querySelector('.stock');
                if (stockEl) stockEl.textContent = item.stock;
            }
            $cart.innerHTML = cartHtml;
            updateCartTotals();
        }

        // --- Category filter ---
        let activeCategory = null;
        window.filterCategory = function(categoryId, btn) {
            const cards = document.querySelectorAll('.category-card');
            const items = document.querySelectorAll('.item-card');
            if (activeCategory === categoryId) {
                activeCategory = null;
                cards.forEach(c => c.classList.remove('active'));
                items.forEach(i => i.parentElement.style.display = '');
            } else {
                activeCategory = categoryId;
                cards.forEach(c => c.classList.remove('active'));
                btn.classList.add('active');
                items.forEach(i => {
                    const itemCat = i.dataset.category;
                    i.parentElement.style.display = (categoryId === 'all' || itemCat == categoryId) ? '' : 'none';
                });
            }
        };

        // --- Item click: open modal or add to cart ---
        document.addEventListener('click', function (e) {
            const card = e.target.closest('.item-card');
            if (!card) return;
            const id = card.dataset.id;
            const name = card.querySelector('.name')?.textContent;
            const price = parseFloat(card.dataset.price) || 0;
            const stock = parseInt(card.dataset.stock) || 0;

            // Already added?
            const existingItem = document.querySelector(`#cart .cart-item[data-itemid="${id}"], #cart .sale-item[data-itemid="${id}"]`);
            if (existingItem) {
                showError('Already added! Use the +/- buttons in the cart to change quantity.');
                return;
            }
            if (stock <= 0 && !(window.recipeProductIds || []).includes(parseInt(id, 10))) {
                showError('Stock out!');
                return;
            }

            // Open item modal
            if (window.Alpine) {
                const modalRoot = document.querySelector('#itemModal')?.closest('[x-data]');
                if (modalRoot && modalRoot._x_dataStack) {
                    modalRoot._x_dataStack[0].open = true;
                }
            }
            // populate modal
            const modal = document.getElementById('itemModal');
            if (modal) {
                modal.querySelector('input[name="id"]').value = id;
                modal.querySelector('input[name="stock"]').value = stock;
                modal.querySelector('input[name="quantity"]').value = 1;
                modal.querySelector('input[name="price"]').value = price;
                const basePrice = modal.querySelector('input[name="base_price"]');
                if (basePrice) basePrice.value = price;
                modal.querySelector('input[name="discount_amount"]').value = 0;
                modal.querySelector('input[name="note"]').value = '';
                const title = document.getElementById('productModalLabel');
                if (title) title.textContent = name;
                renderModifiers(id);
                recalcModalTotal();
            }
        });

        window.recipeProductIds = @json(($recipeProductIds ?? collect())->values());
        const productModifiersMap = @json($productModifiersMap ?? []);

        function selectedModifiers() {
            const modal = document.getElementById('itemModal');
            if (!modal) return [];
            return Array.from(modal.querySelectorAll('input[name="modifier_ids[]"]:checked')).map(el => ({
                id: parseInt(el.value, 10),
                name: el.dataset.name,
                group_name: el.dataset.group,
                price: parseFloat(el.dataset.price) || 0,
            }));
        }

        function modifiersExtra() {
            return selectedModifiers().reduce((sum, m) => sum + (m.price || 0), 0);
        }

        function renderModifiers(productId) {
            const section = document.getElementById('modifiersSection');
            const list = document.getElementById('modifiersList');
            if (!section || !list) return;
            const mods = productModifiersMap[productId] || [];
            if (!mods.length) {
                section.style.display = 'none';
                list.innerHTML = '';
                return;
            }
            section.style.display = '';
            const groups = {};
            mods.forEach(m => {
                const g = m.group_name || 'Options';
                if (!groups[g]) groups[g] = [];
                groups[g].push(m);
            });
            list.innerHTML = Object.keys(groups).map(group => {
                const rows = groups[group].map(m => `
                    <label class="flex items-center gap-2 text-sm text-slate-700 py-0.5">
                        <input type="checkbox" class="rounded border-slate-300 modifier-check"
                               name="modifier_ids[]" value="${m.id}"
                               data-name="${m.name.replace(/"/g, '&quot;')}"
                               data-group="${(m.group_name || '').replace(/"/g, '&quot;')}"
                               data-price="${m.price}"
                               ${m.is_required ? 'checked' : ''}>
                        <span class="flex-1">${m.name}</span>
                        <span class="text-slate-500">+${m.price}</span>
                    </label>
                `).join('');
                return `<div><div class="text-[10px] uppercase tracking-wider text-slate-400 mb-1">${group}</div>${rows}</div>`;
            }).join('');
            list.querySelectorAll('.modifier-check').forEach(el => {
                el.addEventListener('change', recalcModalTotal);
            });
        }

        function recalcModalTotal() {
            const modal = document.getElementById('itemModal');
            if (!modal) return;
            const q = parseFloat(modal.querySelector('input[name="quantity"]').value) || 0;
            const p = parseFloat(modal.querySelector('input[name="price"]').value) || 0;
            const dtype = modal.querySelector('select[name="discount_type"]').value;
            const damount = parseFloat(modal.querySelector('input[name="discount_amount"]').value) || 0;
            const extra = modifiersExtra();
            let discount = dtype === 'amount' ? damount : ((p + extra) * damount / 100);
            const total = (q * (p + extra)) - (discount || 0);
            const t = modal.querySelector('#product-total-price');
            if (t) t.textContent = isNaN(total) ? 0 : total.toFixed(2);
        }

        // --- Modal field listeners ---
        const modalEl = document.getElementById('itemModal');
        if (modalEl) {
            modalEl.querySelectorAll('input[name="quantity"], input[name="price"], input[name="discount_amount"]').forEach(el => {
                el.addEventListener('keyup', recalcModalTotal);
                el.addEventListener('input', recalcModalTotal);
            });
            modalEl.querySelector('select[name="discount_type"]').addEventListener('change', recalcModalTotal);
        }

        // --- Add to cart from modal ---
        window.addItem = function () {
            const modal = document.getElementById('itemModal');
            if (!modal) return;
            const id = modal.querySelector('input[name="id"]').value;
            const quantity = modal.querySelector('input[name="quantity"]').value;
            const price = modal.querySelector('input[name="price"]').value;
            const dtype = modal.querySelector('select[name="discount_type"]').value;
            const damount = parseFloat(modal.querySelector('input[name="discount_amount"]').value) || 0;
            const note = modal.querySelector('input[name="note"]').value || '';
            const mods = selectedModifiers();
            const extra = mods.reduce((s, m) => s + (m.price || 0), 0);
            const lineUnit = (parseFloat(price) || 0) + extra;
            const discount = dtype === 'amount' ? damount : (lineUnit * damount / 100);

            const missingRequired = (productModifiersMap[id] || []).filter(m => m.is_required)
                .some(m => !mods.find(s => s.id === m.id));
            if (missingRequired) {
                showError('Please select required modifiers.');
                return;
            }

            const btn = document.getElementById('addToCartBtn');
            const original = btn.innerHTML;
            btn.innerHTML = '<i class="ri-loader-4-line animate-spin"></i> Adding...';
            btn.disabled = true;

            const url = isSale
                ? "{{ route('seller.pos.saleItem.add') }}"
                : "{{ route('seller.pos.addItem') }}";
            const oid = isSale ? saleOrderId : orderId;

            fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                body: JSON.stringify({
                    order_id: oid,
                    product_id: id,
                    quantity,
                    unit_price: price,
                    discount,
                    note,
                    modifiers: mods,
                })
            })
            .then(r => r.json().then(d => ({ ok: r.ok, d })))
            .then(({ ok, d }) => {
                btn.innerHTML = original;
                btn.disabled = false;
                if (!ok) { showError(d.message || 'Error'); return; }
                setItemToCart(d.data.item, d.data.cart_item_html);
                // Close modal
                const root = modal.closest('[x-data]');
                if (root && root._x_dataStack) root._x_dataStack[0].open = false;
            })
            .catch(err => {
                btn.innerHTML = original;
                btn.disabled = false;
                if (window.PosOffline && !isSale) {
                    const card = document.getElementById('item-' + id);
                    const line = {
                        productId: Number(id),
                        name: card?.querySelector('.name')?.textContent?.trim() || 'Item',
                        quantity: Number(quantity),
                        unitPrice: lineUnit,
                        discount,
                        note,
                        modifiers: mods,
                    };
                    const empty = $cart.querySelector('.empty-state');
                    if (empty) empty.remove();
                    $cart.appendChild(offlineCartElement(line));
                    updateCartTotals();
                    const root = modal.closest('[x-data]');
                    if (root && root._x_dataStack) root._x_dataStack[0].open = false;
                    window.toast?.warning('Item added locally. It will be validated during synchronization.');
                    return;
                }
                showError(err.message || 'Network error');
            });
        };

        // --- Remove item ---
        window.removeItem = function (cartItemId) {
            const localItem = document.getElementById('cart-item-' + cartItemId);
            if (!navigator.onLine || localItem?.dataset.source === 'offline') {
                localItem?.remove();
                updateCartTotals();
                return;
            }

            const url = isSale
                ? "{{ route('seller.pos.saleItem.remove') }}"
                : "{{ route('seller.pos.removeItem') }}";
            fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                body: JSON.stringify({ [isSale ? 'sale_item_id' : 'cart_item_id']: cartItemId })
            })
            .then(r => r.json())
            .then(d => {
                const sel = isSale ? '#sale-item-' : '#cart-item-';
                const el = document.querySelector(sel + cartItemId);
                if (el) el.remove();
                const item = d.data?.item;
                if (item) {
                    const stockEl = document.querySelector('#item-' + item.id + ' .stock');
                    if (stockEl) stockEl.textContent = item.stock;
                }
                updateCartTotals();
            })
            .catch(() => {
                localItem?.remove();
                updateCartTotals();
                window.toast?.warning('Item removed locally and will reconcile during sync.');
            });
        };

        // --- Quantity +/- ---
        document.addEventListener('click', function (e) {
            if (e.target.matches('.increment, .saleIncrement')) {
                const wrap = e.target.parentElement;
                const input = wrap.querySelector('.quantityInput, .saleQuantityInput');
                const cartItem = e.target.closest('.cart-item, .sale-item');
                const itemId = cartItem.dataset.itemid;
                const stock = parseInt(document.querySelector('#item-' + itemId + ' .stock').textContent) || 0;
                if (stock <= 0) {
                    const isRecipe = (window.recipeProductIds || []).includes(parseInt(itemId, 10));
                    if (!isRecipe) { showError('Stock out!'); return; }
                }
                input.value = parseInt(input.value) + 1;
                if (isSale) updateSaleQuantity(cartItem.dataset.id);
                else updateCartQuantity(cartItem.dataset.id);
            }
            if (e.target.matches('.decrement, .saleDecrement')) {
                const wrap = e.target.parentElement;
                const input = wrap.querySelector('.quantityInput, .saleQuantityInput');
                if (parseInt(input.value) <= 1) return;
                input.value = parseInt(input.value) - 1;
                const cartItem = e.target.closest('.cart-item, .sale-item');
                if (isSale) updateSaleQuantity(cartItem.dataset.id);
                else updateCartQuantity(cartItem.dataset.id);
            }
        });

        function updateCartQuantity(cartItemId) {
            const cartItem = document.getElementById('cart-item-' + cartItemId);
            const qty = parseInt(cartItem.querySelector('.quantityInput').value);
            if (!navigator.onLine || cartItem.dataset.source === 'offline') {
                updateOfflineLine(cartItem);
                return;
            }
            fetch("{{ route('seller.pos.updateQuantity') }}", {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                body: JSON.stringify({ cart_item_id: cartItemId, quantity: qty })
            })
            .then(r => r.json())
            .then(d => {
                const item = d.data.item;
                const stockEl = document.querySelector('#item-' + item.id + ' .stock');
                if (stockEl) stockEl.textContent = item.stock;
                cartItem.querySelector('.price').textContent = d.data.cart_item.total_price;
                updateCartTotals();
            })
            .catch(() => {
                updateOfflineLine(cartItem);
                window.toast?.warning('Quantity changed locally and will sync later.');
            });
        }

        function updateSaleQuantity(saleItemId) {
            const saleItem = document.getElementById('sale-item-' + saleItemId);
            const qty = parseInt(saleItem.querySelector('.saleQuantityInput').value);
            fetch("{{ route('seller.pos.saleItem.updateQuantity') }}", {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                body: JSON.stringify({ sale_item_id: saleItemId, quantity: qty })
            })
            .then(r => r.json())
            .then(d => {
                const item = d.data.item;
                const stockEl = document.querySelector('#item-' + item.id + ' .stock');
                if (stockEl) stockEl.textContent = item.stock;
                saleItem.querySelector('.price').textContent = d.data.sale_item.total_price;
                updateCartTotals();
            });
        }

        // --- Discount / Paid ---
        $discountInput.addEventListener('input', updateCheckoutPrice);
        $paidInput.addEventListener('input', updateCheckoutPrice);

        // --- Barcode search ---
        window.handleBarcode = function (code) {
            if (!code) return;
            const card = Array.from(document.querySelectorAll('.item-card')).find(c => c.dataset.code === code);
            if (card) {
                card.click();
                document.getElementById('barcodeInput').value = '';
                // close barcode modal
                const root = document.querySelector('[x-data*="barcodeOpen"]');
                if (root && root._x_dataStack) root._x_dataStack[0].barcodeOpen = false;
            } else {
                showError('No product found with code: ' + code);
            }
        };

        document.getElementById('productCodeInput')?.addEventListener('keyup', function (e) {
            window.handleBarcode(e.target.value);
            if (e.target.value) e.target.value = '';
        });

        // --- Customer form toggle ---
        window.toggleCustomerForm = function () {
            document.getElementById('customerForm')?.classList.toggle('hidden');
        };

        // --- Product name search ---
        document.getElementById('productNameSearch')?.addEventListener('keyup', function (e) {
            const q = e.target.value.toLowerCase();
            document.querySelectorAll('.item-card').forEach(card => {
                const name = card.querySelector('.name')?.textContent.toLowerCase() || '';
                card.parentElement.style.display = name.includes(q) ? '' : 'none';
            });
        });

        // --- Checkout ---
        window.checkout = async function () {
            const btn = document.getElementById('checkoutBtn');
            const original = btn.innerHTML;
            btn.innerHTML = '<i class="ri-loader-4-line animate-spin"></i> Processing...';
            btn.disabled = true;

            if (!window.PosOffline) {
                btn.innerHTML = original;
                btn.disabled = false;
                showError('Offline storage is not ready. Please retry.');
                return;
            }

            const items = offlineCartItems();
            if (items.length === 0) {
                btn.innerHTML = original;
                btn.disabled = false;
                showError('No items added!');
                return;
            }

            const clientOrderId = window.PosOffline.uuid();
            const deviceId = await window.PosOffline.deviceId();
            const createdAtClient = new Date().toISOString();
            const requestPayload = {
                    order_id: orderId,
                    customer_id: $customerSelect?.value,
                    customer_name: $customerName?.value,
                    customer_phone: $customerPhone?.value,
                    table_id: $tableSelect?.value,
                    employee_id: $employeeSelect?.value,
                    discount_amount: $discountInput.value,
                    paid_amount: $paidInput.value,
                    note: $note.value,
                    payment_type: 'cash',
                    client_order_id: clientOrderId,
                    device_id: deviceId,
                    created_at_client: createdAtClient,
            };

            const saveOffline = async () => {
                const order = makeOfflineOrder(clientOrderId, deviceId);
                order.created_at_client = createdAtClient;
                await window.PosOffline.queueOrder(order);
                $cart.innerHTML = `<div class="empty-state py-10">
                    <i class="ri-cloud-off-line"></i>
                    <h3>Order saved offline</h3>
                    <p>Reference: ${clientOrderId.slice(0, 8).toUpperCase()}</p>
                </div>`;
                updateCartTotals();
                window.toast?.warning('Order saved offline and pending synchronization.', 7000);
                btn.innerHTML = original;
                btn.disabled = true;
            };

            if (!navigator.onLine) {
                try {
                    await saveOffline();
                } catch (error) {
                    btn.innerHTML = original;
                    btn.disabled = false;
                    showError('Could not save the offline order: ' + error.message);
                }
                return;
            }

            try {
                const response = await fetch("{{ route('seller.pos.checkout') }}", {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                    body: JSON.stringify(requestPayload),
                });
                const data = await response.json().catch(() => ({}));

                if (!response.ok) {
                    if (response.status >= 500) {
                        await saveOffline();
                        return;
                    }
                    btn.innerHTML = original;
                    btn.disabled = false;
                    showError(data.message || 'Checkout failed');
                    return;
                }

                const url = "{{ route('seller.sales.invoice', $cart->order_id) }}";
                window.open(url, 'Invoice', 'width=800,height=600,scrollbars=yes,resizable=yes');
                setTimeout(() => window.location.reload(), 200);
            } catch (error) {
                try {
                    await saveOffline();
                } catch (storageError) {
                    btn.innerHTML = original;
                    btn.disabled = false;
                    showError('Network failed and the order could not be saved: ' + storageError.message);
                }
            }
        };

        // --- Hold ---
        window.hold = function () {
            const btn = document.getElementById('holdBtn');
            const original = btn.innerHTML;
            btn.innerHTML = '<i class="ri-loader-4-line animate-spin"></i> Saving...';
            btn.disabled = true;

            fetch("{{ route('seller.pos.hold') }}", {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                body: JSON.stringify({
                    order_id: orderId,
                    customer_id: $customerSelect?.value,
                    customer_name: $customerName?.value,
                    customer_phone: $customerPhone?.value,
                    table_id: $tableSelect?.value,
                    employee_id: $employeeSelect?.value,
                    discount_amount: $discountInput.value,
                    paid_amount: $paidInput.value,
                    note: $note.value,
                })
            })
            .then(r => r.json().then(d => ({ ok: r.ok, d })))
            .then(({ ok, d }) => {
                if (!ok) { btn.innerHTML = original; btn.disabled = false; showError(d.message || 'Error'); return; }
                setTimeout(() => window.location.reload(), 200);
            })
            .catch(err => { btn.innerHTML = original; btn.disabled = false; showError(err.message || 'Network error'); });
        };

        // --- Update Sale ---
        window.updateSale = function () {
            const btn = document.getElementById('updateSaleBtn');
            const original = btn.innerHTML;
            btn.innerHTML = '<i class="ri-loader-4-line animate-spin"></i> Updating...';
            btn.disabled = true;

            fetch("{{ route('seller.pos.updateSale') }}", {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                body: JSON.stringify({
                    order_id: saleOrderId,
                    customer_id: $customerSelect?.value,
                    customer_name: $customerName?.value,
                    customer_phone: $customerPhone?.value,
                    table_id: $tableSelect?.value,
                    employee_id: $employeeSelect?.value,
                    discount_amount: $discountInput.value,
                    paid_amount: $paidInput.value,
                    note: $note.value,
                })
            })
            .then(r => r.json().then(d => ({ ok: r.ok, d })))
            .then(({ ok, d }) => {
                if (!ok) { btn.innerHTML = original; btn.disabled = false; showError(d.message || 'Error'); return; }
                const url = "{{ $sale ? route('seller.sales.invoice', $sale->order_id) : '#' }}";
                window.open(url, 'Invoice', 'width=800,height=600,scrollbars=yes,resizable=yes');
                setTimeout(() => window.location.href = "{{ route('seller.pos.index') }}", 200);
            })
            .catch(err => { btn.innerHTML = original; btn.disabled = false; showError(err.message || 'Network error'); });
        };

        // --- Fullscreen ---
        document.getElementById('fullscreen-btn')?.addEventListener('click', function () {
            if (!document.fullscreenElement) document.documentElement.requestFullscreen();
            else document.exitFullscreen();
        });

        // --- Refresh ---
        document.getElementById('refresh-btn')?.addEventListener('click', () => location.reload());

        // --- Real-time kitchen / order updates ---
        (function subscribePosEcho() {
            if (!window.Echo) return;
            const sellerId = {{ (int) auth()->id() }};
            const readyBadge = document.getElementById('posKitchenReadyBadge');
            let readyCount = 0;

            function setReadyCount(n) {
                readyCount = Math.max(0, n);
                if (!readyBadge) return;
                if (readyCount > 0) {
                    readyBadge.textContent = String(readyCount);
                    readyBadge.classList.remove('hidden');
                } else {
                    readyBadge.classList.add('hidden');
                }
            }

            window.Echo.private(`seller.${sellerId}.pos`)
                .listen('.OrderPlaced', (e) => {
                    if (window.toast) {
                        window.toast.info(`Sent to kitchen: ${e.table_name || e.ticket_number}`);
                    }
                })
                .listen('.KitchenStatusUpdated', (e) => {
                    if (e.status === 'ready') {
                        setReadyCount(readyCount + 1);
                        if (window.toast) {
                            window.toast.success(`Ready: ${e.table_name || e.ticket_number}`);
                        }
                    }
                })
                .listen('.TableStatusChanged', (e) => {
                    const chip = document.querySelector(`.dining-table-chip[data-table-id="${e.table_id}"]`);
                    if (!chip || !e.status) return;
                    chip.dataset.status = e.status;
                    const statusEl = chip.querySelector('.dining-table-card-status');
                    if (statusEl) statusEl.textContent = e.status.charAt(0).toUpperCase() + e.status.slice(1);
                });
        })();

        // --- Offline status and durable queue badge ---
        const offlineBanner = document.getElementById('offlineStatusBanner');
        const offlinePendingCount = document.getElementById('offlinePendingCount');
        const offlineSyncButton = document.getElementById('offlineSyncButton');
        const offlineSyncBadge = document.getElementById('offlineSyncBadge');

        async function refreshOfflineStatus() {
            const offline = !navigator.onLine;
            offlineBanner?.classList.toggle('hidden', !offline);

            if (!window.PosOffline) return;
            const count = await window.PosOffline.pendingCount();
            offlineSyncButton?.classList.toggle('hidden', count === 0);
            if (offlineSyncBadge) offlineSyncBadge.textContent = String(count);
            if (offlinePendingCount) {
                offlinePendingCount.textContent = `${count} pending`;
                offlinePendingCount.classList.toggle('hidden', count === 0);
            }
        }

        offlineSyncButton?.addEventListener('click', async () => {
            offlineSyncButton.disabled = true;
            await window.PosOffline?.drain();
            await refreshOfflineStatus();
            offlineSyncButton.disabled = false;
        });
        window.addEventListener('online', () => {
            window.toast?.info('Connection restored. Synchronizing pending orders…');
            refreshOfflineStatus();
        });
        window.addEventListener('offline', refreshOfflineStatus);
        window.addEventListener('pos-offline-queue-changed', refreshOfflineStatus);
        navigator.serviceWorker?.addEventListener('message', event => {
            if (event.data?.type === 'POS_OFFLINE_QUEUE_CHANGED') refreshOfflineStatus();
        });

        // Initial
        updateCartTotals();
        refreshOfflineStatus();
    });
</script>
@endpush

@endsection
