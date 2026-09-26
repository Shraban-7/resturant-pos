@extends('layouts.pos')
@section('title', __('admin.pos.terminal'))

@section('content')

    @php
        $subtotal = $totalPrice = 0;
    @endphp

    <x-error-modal />

    <div class="flex flex-col h-screen w-screen bg-slate-100 overflow-hidden select-none" x-data="posApp()" x-cloak>
        {{-- =================== OFFLINE STATUS BANNER =================== --}}
        <div id="offlineStatusBanner"
            class="hidden shrink-0 px-4 py-2 text-xs font-bold bg-amber-400 text-amber-950 border-b border-amber-500 shadow-sm z-30">
            <div class="max-w-7xl mx-auto flex items-center justify-between gap-3">
                <span class="flex items-center gap-2"><i class="ri-wifi-off-line text-base"></i> {{ __('admin.pos.offline_banner') }}</span>
                <span id="offlinePendingCount"
                    class="bg-amber-950 text-white text-[10px] px-2 py-0.5 rounded-full font-extrabold hidden"></span>
            </div>
        </div>

        {{-- =================== TOP WORKSTATION BAR =================== --}}
        <header
            class="bg-slate-950 text-white h-16 flex items-center gap-3 px-4 shrink-0 z-20 border-b border-slate-800 shadow-md">
            <!-- Logo & Cashier Profile -->
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 shrink-0 group">
                @if (store_logo_url())
                    <img src="{{ store_logo_url() }}" alt="{{ store_name() }}"
                        class="h-9 w-9 rounded-xl object-cover ring-1 ring-white/20 shrink-0">
                @else
                    <span
                        class="flex items-center justify-center h-9 w-9 rounded-xl bg-gradient-to-br from-orange-500 to-orange-700 text-white shadow-md shadow-orange-950/40">
                        <i class="ri-restaurant-2-line text-lg"></i>
                    </span>
                @endif
                <div class="hidden sm:block">
                    <div class="text-sm font-extrabold text-white leading-tight tracking-tight flex items-center gap-1.5">
                        <span>{{ __('admin.pos.terminal') }}</span>
                        <span class="h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    </div>
                    <div class="text-[11px] text-slate-400 font-medium flex items-center gap-1.5 mt-0.5">
                        <span
                            class="text-orange-400 font-bold uppercase tracking-wider text-[10px]">{{ auth()->user()->name }}</span>
                        <span>·</span>
                        <span id="posLiveClock" class="font-mono text-slate-300"></span>
                    </div>
                </div>
            </a>

            <!-- Search Bar -->
            <div class="flex-1 max-w-lg mx-2">
                <div class="relative">
                    <i
                        class="ri-search-2-line absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none text-base"></i>
                    <input id="productNameSearch" type="text"
                        class="block w-full rounded-xl border border-slate-700/80 bg-slate-900/90 px-3.5 py-2 pl-10 pr-10 text-xs sm:text-sm text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all shadow-inner"
                        placeholder="{{ __('admin.pos.search_placeholder') }}">
                    <span
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-[10px] font-mono font-bold text-slate-500 bg-slate-800 px-1.5 py-0.5 rounded border border-slate-700">ESC</span>
                </div>
            </div>

            <!-- Branch Switcher -->
            @if (($branches ?? collect())->isNotEmpty())
                <form action="{{ route('admin.branches.switch') }}" method="post" class="hidden md:block">
                    @csrf
                    <div class="relative flex items-center">
                        <i
                            class="ri-store-2-line absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
                        <select name="branch_id"
                            class="rounded-xl border border-slate-700 bg-slate-900/90 pl-8 pr-7 py-1.5 text-xs font-semibold text-slate-200 focus:outline-none focus:ring-2 focus:ring-orange-500 transition-all cursor-pointer"
                            onchange="this.form.submit()" title="{{ __('admin.pos.active_branch') }}">
                            <option value="" @selected(is_all_branches_mode())>{{ __('admin.navbar.all_branches') }}</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}" @selected((int) active_branch_id() === (int) $branch->id && !is_all_branches_mode())>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </form>
            @endif

            <!-- Quick Action Toolbar -->
            <div class="flex items-center gap-1.5 ml-auto">
                <!-- Kitchen Display Link with Ready Badge -->
                <a href="{{ route('admin.kds.index') }}"
                    class="relative inline-flex items-center justify-center h-9 w-9 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800 transition"
                    title="{{ __('admin.sidebar.kitchen_display') }}" id="posKitchenBadgeLink">
                    <i class="ri-macbook-line text-lg"></i>
                    <span id="posKitchenReadyBadge"
                        class="absolute -top-0.5 -right-0.5 min-w-[1.15rem] h-[1.15rem] px-1 rounded-full bg-emerald-500 text-white text-[10px] font-extrabold leading-[1.15rem] text-center shadow hidden">0</span>
                </a>

                <!-- Offline Sync Button -->
                <button type="button" id="offlineSyncButton"
                    class="relative inline-flex items-center justify-center h-9 w-9 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800 transition hidden"
                    title="{{ __('admin.pos.sync_offline') }}">
                    <i class="ri-cloud-line text-lg"></i>
                    <span id="offlineSyncBadge"
                        class="absolute -top-0.5 -right-0.5 min-w-[1.15rem] h-[1.15rem] px-1 rounded-full bg-amber-500 text-white text-[10px] font-extrabold leading-[1.15rem] text-center shadow">0</span>
                </button>

                <!-- Quick Barcode Scan Modal -->
                <button type="button" id="productCodeBtn"
                    class="inline-flex items-center justify-center h-9 w-9 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800 transition"
                    title="{{ __('admin.pos.scan_code') }}"
                    @click="barcodeOpen = true; $nextTick(() => document.getElementById('barcodeInput')?.focus())">
                    <i class="ri-barcode-line text-lg"></i>
                </button>

                <!-- Fullscreen -->
                <button type="button" id="fullscreen-btn"
                    class="hidden sm:inline-flex items-center justify-center h-9 w-9 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800 transition"
                    title="{{ __('admin.pos.fullscreen') }}">
                    <i class="ri-fullscreen-line text-lg"></i>
                </button>

                <!-- Refresh -->
                <button type="button" id="refresh-btn"
                    class="hidden sm:inline-flex items-center justify-center h-9 w-9 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800 transition"
                    title="{{ __('admin.pos.refresh_terminal') }}">
                    <i class="ri-loop-right-line text-lg"></i>
                </button>

                <!-- Dashboard -->
                <a href="{{ route('admin.dashboard') }}"
                    class="hidden sm:inline-flex items-center justify-center h-9 w-9 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800 transition"
                    title="{{ __('admin.pos.admin_dashboard') }}">
                    <i class="ri-dashboard-line text-lg"></i>
                </a>

                <!-- Logout -->
                <a href="{{ route('logout') }}"
                    class="inline-flex items-center justify-center h-9 w-9 rounded-xl text-slate-400 hover:text-red-300 hover:bg-red-500/20 transition"
                    title="{{ __('admin.pos.sign_out') }}">
                    <i class="ri-logout-box-r-line text-lg"></i>
                </a>
            </div>
        </header>

        {{-- =================== MAIN WORKSPACE SPLIT =================== --}}
        <div class="flex-1 flex overflow-hidden">

            {{-- ===== LEFT CANVAS: ORDER STATUS + CATEGORIES + PRODUCTS ===== --}}
            <main class="flex-1 overflow-y-auto p-4 lg:p-5 pb-24 lg:pb-6 space-y-5">

                  {{-- Categories Filter Tabs Carousel --}}
                  <div>
                      <div class="flex items-center justify-between mb-2">
                          <h3
                              class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                              {{ __('admin.pos.categories') }}
                          </h3>
                          @if(count($recentSales ?? []) > 0)
                              <button type="button"
                                  class="text-[11px] font-bold text-orange-600 hover:text-orange-700 flex items-center gap-1 shrink-0"
                                  @click="$dispatch('open-recent-sales')">
                                  <i class="ri-receipt-2-line"></i> {{ __('admin.pos.recent_orders') }}
                                  <span class="bg-orange-100 text-orange-700 text-[10px] font-extrabold px-1.5 py-0.2 rounded-full">{{ count($recentSales) }}</span>
                              </button>
                          @endif
                      </div>

                      <div class="flex gap-1.5 overflow-x-auto no-scrollbar py-1 -mx-1 px-1" id="categoryScroll">
                          <button
                              class="category-card shrink-0 active inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-white border border-slate-200 text-slate-600 font-semibold text-[11px] shadow-sm hover:border-orange-400 hover:bg-orange-50 transition-all cursor-pointer"
                              data-category="all" onclick="window.filterCategory('all', this)" type="button">
                              {{ __('admin.pos.all_items') }}
                          </button>
                          @foreach ($categories as $category)
                              <button
                                  class="category-card shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-white border border-slate-200 text-slate-600 font-semibold text-[11px] shadow-sm hover:border-orange-400 hover:bg-orange-50 transition-all cursor-pointer"
                                  data-category="{{ $category->id }}"
                                  onclick="window.filterCategory({{ $category->id }}, this)" type="button">
                                  {{ $category->name }}
                              </button>
                          @endforeach
                      </div>
                  </div>

                  {{-- Running Orders (compact, under categories) --}}
                  <div class="flex items-center gap-2 mb-2">
                      <h3
                          class="text-[11px] font-bold text-slate-500 uppercase tracking-wider flex items-center gap-1.5">
                          <i class="ri-restart-line text-orange-600"></i> {{ __('admin.pos.running_orders') }}
                      </h3>
                      <span class="text-[10px] font-extrabold px-1.5 py-0.2 rounded-full {{ count($runningSales ?? []) > 0 ? 'bg-orange-100 text-orange-700' : 'bg-slate-200 text-slate-500' }}">
                          {{ count($runningSales ?? []) }}
                      </span>
                  </div>
                  @if(count($runningSales ?? []) > 0)
                      <div class="flex items-center gap-2 overflow-x-auto no-scrollbar py-0.5 -mx-1 px-1 mb-4">
                          @foreach ($runningSales as $sale)
                              @php
                                  $chipTableName = ($sale->getRelationValue('diningTable') ?? $sale->getRelationValue('table'))?->name ?? $sale->diningTable?->name ?? $sale->table?->name ?? null;
                              @endphp
                              <a href="{{ route('admin.pos.index', ['sale' => $sale->order_id]) }}"
                                 class="shrink-0 inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-white border border-slate-200/90 hover:border-orange-400 hover:bg-orange-50/50 shadow-sm transition-all group">
                                  <span class="text-[11px] font-bold font-mono text-slate-500 group-hover:text-orange-600">#{{ $sale->order_id }}</span>
                                  @if($chipTableName)
                                      <span class="text-[10px] font-semibold text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded-md border border-emerald-200/60 flex items-center gap-0.5">
                                          <i class="ri-restaurant-line text-[9px]"></i>{{ $chipTableName }}
                                      </span>
                                  @endif
                              </a>
                          @endforeach
                      </div>
                  @endif

                 {{-- Products Menu Grid --}}
                 <div>
                     <div class="flex items-center justify-between mb-2">
                         <h3
                             class="text-[11px] font-bold text-slate-500 uppercase tracking-wider flex items-center gap-1.5">
                             <i class="ri-restaurant-2-line text-orange-600"></i> {{ __('admin.pos.menu_items') }}
                         </h3>
                         <span class="text-[11px] text-slate-400 font-medium">{{ __('admin.pos.items_available', ['count' => count($products)]) }}</span>
                     </div>
                     <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-2 sm:gap-2.5"
                         id="productsGrid">
                         @foreach ($products as $product)
                             <x-pos.item :item="$product" :available="$product->pos_available" :unlimited="$product->pos_unlimited" :addons-count="count($productAddonsMap[$product->id] ?? [])" />
                         @endforeach
                     </div>
                 </div>
            </main>

            {{-- ===== RIGHT PANEL: DESKTOP TICKET & CART ===== --}}
            <aside
                class="hidden lg:flex w-[400px] xl:w-[450px] bg-white border-l border-slate-200/90 flex-col shrink-0 shadow-xl shadow-slate-200/50">
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
                    'vatConfig' => $vatConfig ?? ['mode' => 'disabled', 'rate' => 0],
                ])
            </aside>
        </div>

        {{-- =================== MOBILE FLOATING CART BUTTON =================== --}}
        <button
            class="lg:hidden fixed bottom-4 left-4 right-4 z-30 bg-slate-950 text-white shadow-2xl shadow-slate-950/50 rounded-2xl h-14 px-5 flex items-center justify-between font-bold text-sm active:scale-95 transition"
            @click="cartOpen = true">
            <div class="flex items-center gap-2.5">
                <span class="flex items-center justify-center h-8 w-8 rounded-xl bg-orange-600 text-white">
                    <i class="ri-shopping-cart-2-line text-lg"></i>
                </span>
                <span>{{ __('admin.pos.view_current_ticket') }}</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="px-2 py-0.5 rounded-full bg-orange-600 text-xs font-extrabold" id="mobileCartCount">0</span>
                <i class="ri-arrow-up-s-line text-lg"></i>
            </div>
        </button>

        {{-- =================== MOBILE CART DRAWER =================== --}}
        <div x-show="cartOpen" x-transition.opacity class="lg:hidden fixed inset-0 z-40" style="display:none"
            @keydown.escape.window="cartOpen = false">
            <div class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm" @click="cartOpen = false"></div>
            <aside
                class="absolute bottom-0 left-0 right-0 bg-white rounded-t-3xl max-h-[92vh] flex flex-col shadow-2xl overflow-hidden"
                x-transition:enter="transition transform ease-out duration-300"
                x-transition:enter-start="translate-y-full" x-transition:enter-end="translate-y-0"
                x-transition:leave="transition transform ease-in duration-200" x-transition:leave-start="translate-y-0"
                x-transition:leave-end="translate-y-full">
                <div class="flex items-center justify-between px-5 py-3.5 border-b border-slate-200 bg-slate-50">
                    <h2 class="text-sm font-extrabold text-slate-900 flex items-center gap-2">
                        <i class="ri-receipt-line text-orange-600"></i> {{ __('admin.pos.order_ticket') }}
                    </h2>
                    <button type="button"
                        class="h-8 w-8 rounded-full hover:bg-slate-200 text-slate-500 flex items-center justify-center transition"
                        @click="cartOpen = false">
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
                    'vatConfig' => $vatConfig ?? ['mode' => 'disabled', 'rate' => 0],
                    ])
                </div>
            </aside>
        </div>

        {{-- =================== BARCODE MODAL =================== --}}
        <div x-show="barcodeOpen" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-4"
            style="display:none" @keydown.escape.window="barcodeOpen = false"
            @close-barcode.window="barcodeOpen = false">
            <div class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm" @click="barcodeOpen = false"></div>
            <div class="relative w-full max-w-md bg-white rounded-3xl shadow-2xl border border-slate-200 p-6 overflow-hidden"
                @click.stop>
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <span class="flex items-center justify-center h-8 w-8 rounded-xl bg-orange-100 text-orange-600">
                            <i class="ri-barcode-line text-lg"></i>
                        </span>
                        <h3 class="text-base font-bold text-slate-900">{{ __('admin.pos.quick_barcode') }}</h3>
                    </div>
                    <button type="button"
                        class="h-8 w-8 rounded-full hover:bg-slate-100 text-slate-400 hover:text-slate-700 flex items-center justify-center transition"
                        @click="barcodeOpen = false">
                        <i class="ri-close-line text-xl"></i>
                    </button>
                </div>
                <div class="relative">
                    <i class="ri-qr-code-line absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg"></i>
                    <input type="text" id="barcodeInput"
                        class="w-full border border-slate-200 rounded-2xl pl-11 pr-4 py-3 text-base font-mono font-bold text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-orange-500 bg-slate-50"
                        placeholder="{{ __('admin.pos.scan_placeholder') }}" @keyup.enter="handleBarcode($event.target.value)">
                </div>
                <p class="mt-3 text-xs text-slate-500 leading-relaxed">{!! __('admin.pos.barcode_helper') !!}</p>
            </div>
        </div>

        {{-- Item Customization Modal (Alpine) --}}
        <x-pos.item-modal />

        {{-- Recent Sales Modal --}}
        <x-pos.recent-sales-modal :sales="$recentSales" />

    </div>

    @push('styles')
        <style>
            /* In-ticket quantity pill on product cards (plain CSS: no frontend rebuild needed) */
            .ticket-qty-tag {
                display: inline-flex;
                align-items: center;
                gap: 2px;
                border-radius: 9999px;
                background: #059669;
                color: #fff;
                font-size: 10px;
                font-weight: 800;
                line-height: 1;
                padding: 4px 7px;
                box-shadow: 0 2px 6px rgb(5 150 105 / 0.4);
            }
        </style>
    @endpush

    @push('footer')
        <script>
            window.POS_OFFLINE_CONFIG = {
                admin_id: {{ (int) panel_owner_id() }},
                currency: 'BDT',
                products: {!! json_encode($offlineProducts ?? []) !!},
                categories: {!! json_encode($offlineCategories ?? []) !!},
                tables: {!! json_encode($offlineTables ?? []) !!},
                floors: {!! json_encode($offlineFloors ?? []) !!},
                customers: {!! json_encode($offlineCustomers ?? []) !!},
            };

            function posApp() {
                return {
                    cartOpen: false,
                    barcodeOpen: false,
                };
            }

            // Live terminal clock
            (function updateClock() {
                const el = document.getElementById('posLiveClock');
                if (el) {
                    const d = new Date();
                    el.textContent = d.toLocaleTimeString([], {
                        hour: '2-digit',
                        minute: '2-digit',
                        second: '2-digit'
                    });
                }
                setTimeout(updateClock, 1000);
            })();

            document.addEventListener('DOMContentLoaded', function() {
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
                const $giftCardCode = document.getElementById('giftCardCodeInput');
                const $giftCardStatus = document.getElementById('giftCardStatus');
                const $customerName = document.getElementById('customer_name');
                const $customerPhone = document.getElementById('customer_phone');
                const $note = document.getElementById('note');
                const $mobileCartCount = document.getElementById('mobileCartCount');

                function showError(msg) {
                    window.dispatchEvent(new CustomEvent('open-error', {
                        detail: msg
                    }));
                }

                // --- POS order mode: dine-in (table order) vs counter (normal POS order) ---
                @php
                    $posSaleType = $sale?->order_type instanceof \App\Enums\OrderType
                        ? $sale->order_type->value
                        : ($sale?->order_type ?? null);
                @endphp
                window.posOrderMode = localStorage.getItem('pos_order_mode') || @json($posSaleType === 'counter' ? 'counter' : 'dine_in');
                if (window.posOrderMode !== 'dine_in' && window.posOrderMode !== 'counter') {
                    window.posOrderMode = 'dine_in';
                }

                window.setOrderMode = function(mode) {
                    if (mode !== 'dine_in' && mode !== 'counter') return;
                    window.posOrderMode = mode;
                    try {
                        localStorage.setItem('pos_order_mode', mode);
                    } catch (_) {}
                    document.querySelectorAll('[data-order-mode-btn]').forEach(btn => {
                        const active = btn.dataset.orderModeBtn === mode;
                        btn.classList.toggle('bg-slate-900', active);
                        btn.classList.toggle('text-white', active);
                        btn.classList.toggle('shadow', active);
                        btn.classList.toggle('text-slate-500', !active);
                    });
                    const isDine = mode === 'dine_in';
                    document.querySelectorAll('.js-table-wrap').forEach(el => {
                        el.style.display = isDine ? '' : 'none';
                    });
                };
                window.setOrderMode(window.posOrderMode);

                function parseJsonAttribute(value, fallback = []) {
                    if (!value) return fallback;
                    try {
                        return JSON.parse(value);
                    } catch (_) {
                        return fallback;
                    }
                }

                function offlineCartItems() {
                    return Array.from(document.querySelectorAll('#cart .cart-item')).map(el => ({
                        product_id: Number(el.dataset.itemid),
                        quantity: Number(el.querySelector('.quantityInput')?.value || 0),
                        unit_price_snapshot: Number(el.dataset.unitPrice || 0),
                        discount: Number(el.dataset.discount || 0),
                        modifiers: parseJsonAttribute(el.dataset.modifiers),
                        addons: parseJsonAttribute(el.dataset.addons),
                    })).filter(item => item.product_id && item.quantity > 0);
                }

                function makeOfflineOrder(clientOrderId, deviceId) {
                    const subtotal = offlineCartItems().reduce(
                        (sum, item) => sum + Math.max(0, item.unit_price_snapshot * item.quantity - item.discount),
                        0
                    );
                    const discount = Number($discountInput.value || 0);
                    const offlineVat = vatFor(subtotal, discount);
                    const offlineNet = Math.max(0, subtotal - discount);
                    const payable = offlineVat.mode === 'exclusive' ? offlineNet + offlineVat.vat : offlineNet;
                    const paid = Number($paidInput.value || 0);
                    const isDineMode = window.posOrderMode === 'dine_in';
                    const tableId = isDineMode ? (Number($tableSelect?.value || 0) || null) : null;

                    return {
                        client_order_id: clientOrderId,
                        device_id: deviceId,
                        admin_id: {{ (int) auth()->id() }},
                        source_order_id: orderId,
                        channel: isDineMode ? 'dine_in' : 'counter',
                        dining_table_id: tableId,
                        customer_id: Number($customerSelect?.value || 0) || null,
                        customer_name: $customerName?.value || null,
                        customer_phone: $customerPhone?.value || null,
                        employee_id: Number($employeeSelect?.value || 0) || null,
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
                    element.className =
                        'cart-item bg-white border border-amber-300 rounded-xl p-2.5 flex items-center gap-3 shadow-sm';
                    element.id = `cart-item-${temporaryId}`;
                    Object.assign(element.dataset, {
                        id: temporaryId,
                        itemid: String(line.productId),
                        name: line.name,
                        unitPrice: String(line.unitPrice),
                        discount: String(line.discount),
                        modifiers: JSON.stringify(line.modifiers || []),
                        addons: JSON.stringify(line.addons || []),
                        source: 'offline',
                    });

                    const details = document.createElement('div');
                    details.className = 'flex-1 min-w-0';
                    const name = document.createElement('div');
                    name.className = 'text-xs sm:text-sm font-bold text-slate-800 truncate leading-snug';
                    name.textContent = line.name;
                    details.appendChild(name);
                    if (line.modifiers?.length) {
                        const modifiers = document.createElement('div');
                        modifiers.className =
                            'text-[10px] text-orange-700 bg-orange-50 px-1.5 py-0.5 rounded inline-block truncate max-w-full mt-0.5 font-medium';
                        modifiers.textContent = line.modifiers.map(item => item.name).join(', ');
                        details.appendChild(modifiers);
                    }
                    if (line.addons?.length) {
                        const addons = document.createElement('div');
                        addons.className =
                            'text-[10px] text-sky-700 bg-sky-50 px-1.5 py-0.5 rounded inline-block truncate max-w-full mt-0.5 font-medium';
                        addons.textContent = '⊕ ' + line.addons.map(item => item.name).join(', ');
                        details.appendChild(addons);
                    }
                    const controls = document.createElement('div');
                    controls.className = 'flex items-center gap-1 mt-1.5';
                    controls.innerHTML =
                        `<button type="button" class="qty-btn decrement inline-flex items-center justify-center h-6 w-6 rounded-md border border-slate-200 bg-slate-50 text-slate-700 hover:bg-slate-200 transition"><i class="ri-subtract-line text-xs pointer-events-none"></i></button>
                <input type="text" class="quantityInput qty-input h-6 w-8 text-center text-xs font-bold text-slate-800 border-0 bg-transparent focus:ring-0 p-0" value="${line.quantity}" readonly>
                <button type="button" class="qty-btn increment inline-flex items-center justify-center h-6 w-6 rounded-md border border-slate-200 bg-slate-50 text-slate-700 hover:bg-slate-200 transition"><i class="ri-add-line text-xs pointer-events-none"></i></button>`;
                    details.appendChild(controls);

                    const side = document.createElement('div');
                    side.className = 'text-right shrink-0 flex flex-col items-end justify-between self-stretch';
                    const price = document.createElement('div');
                    price.className = 'text-xs sm:text-sm font-extrabold text-slate-900 tracking-tight price';
                    price.textContent = String((line.unitPrice * line.quantity) - line.discount);
                    const remove = document.createElement('button');
                    remove.type = 'button';
                    remove.className = 'text-slate-400 hover:text-red-600 transition p-1 rounded hover:bg-red-50';
                    remove.innerHTML = '<i class="ri-delete-bin-line text-sm"></i>';
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
                    const total = Math.max(0, Number(cartItem.dataset.unitPrice || 0) * quantity - Number(cartItem
                        .dataset.discount || 0));
                    const price = cartItem.querySelector('.price');
                    if (price) price.textContent = total.toFixed(2);
                    updateCartTotals();
                }

                function refreshCardQtyBadges() {
                    // The ticket renders twice (desktop panel + mobile drawer):
                    // count each line once by element id so quantities stay accurate.
                    const seen = new Set();
                    const qtyByProduct = {};
                    document.querySelectorAll('#cart .cart-item, #cart .sale-item').forEach(el => {
                        const key = el.id || ((el.classList.contains('sale-item') ? 'sale-' : 'cart-') + (el.dataset.id || ''));
                        if (seen.has(key)) return;
                        seen.add(key);
                        const pid = el.dataset.itemid;
                        if (!pid) return;
                        const qtyEl = el.querySelector('.quantityInput, .saleQuantityInput');
                        qtyByProduct[pid] = (qtyByProduct[pid] || 0) + (parseInt(qtyEl?.value) || 0);
                    });
                    document.querySelectorAll('.item-card').forEach(card => {
                        const badge = card.querySelector('[data-qty-badge]');
                        if (!badge) return;
                        const q = qtyByProduct[card.dataset.id] || 0;
                        badge.style.display = q > 0 ? '' : 'none';
                        const n = badge.querySelector('span');
                        if (n) n.textContent = q;
                    });
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
                    refreshCardQtyBadges();
                    if ($subtotal) $subtotal.textContent = total;
                    if ($totalPrice) $totalPrice.textContent = total;
                    if ($mobileCartCount) $mobileCartCount.textContent = count;
                    const itemsCountEl = document.getElementById('itemsCount');
                    if (itemsCountEl) itemsCountEl.textContent = `${count} ${count === 1 ? 'item' : 'items'}`;
                    updateCheckoutPrice();
                }

                function updateCheckoutPrice() {
                    let subtotal = parseFloat($subtotal.textContent) || 0;
                    let discount = parseFloat($discountInput.value) || 0;
                    let paid = parseFloat($paidInput.value) || 0;
                    const calc = vatFor(subtotal, discount);
                    const net = Math.max(0, subtotal - discount);
                    const total = calc.mode === 'exclusive' ? net + calc.vat : net;
                    // The ticket panel renders twice (desktop + mobile drawer):
                    // update every copy so the VAT calculation shows on both.
                    document.querySelectorAll('#vatAmount').forEach(vatAmount => {
                        vatAmount.textContent = calc.vat.toFixed(2);
                    });
                    document.querySelectorAll('#vatLabel').forEach(vatLabel => {
                        const tpl = calc.mode === 'inclusive'
                            ? vatLabel.dataset.inclusive
                            : (calc.mode === 'exclusive' ? vatLabel.dataset.exclusive : vatLabel.dataset.disabled);
                        vatLabel.textContent = (tpl || 'VAT').replace(':rate', calc.rate);
                    });
                    document.querySelectorAll('#vatSign').forEach(vatSign => {
                        vatSign.textContent = calc.mode === 'inclusive' ? '⊂' : (calc.mode === 'exclusive' ? '+' : '–');
                    });
                    $totalPrice.textContent = round2(total).toFixed(2);
                    $due.textContent = round2(total - paid).toFixed(2);
                }

                function setItemToCart(item, cartHtml) {
                    const itemEl = document.getElementById('item-' + item.id);
                    if (itemEl && !item.unlimited) {
                        const stockEl = itemEl.querySelector('.stock');
                        if (stockEl) stockEl.textContent = item.stock;
                        itemEl.dataset.stock = item.stock;
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
                        cards.forEach(c => {
                            c.classList.remove('active');
                            c.classList.remove('!bg-orange-600', '!text-white', '!border-orange-600',
                                'shadow-md');
                        });
                        items.forEach(i => i.style.display = '');
                    } else {
                        activeCategory = categoryId;
                        cards.forEach(c => {
                            c.classList.remove('active');
                            c.classList.remove('!bg-orange-600', '!text-white', '!border-orange-600',
                                'shadow-md');
                        });
                        btn.classList.add('active');
                        btn.classList.add('!bg-orange-600', '!text-white', '!border-orange-600', 'shadow-md');
                        items.forEach(i => {
                            const itemCat = i.dataset.category;
                            i.style.display = (categoryId === 'all' || itemCat == categoryId) ? '' : 'none';
                        });
                    }
                };

                // --- Item click: open modal or add to cart ---
                document.addEventListener('click', function(e) {
                    const card = e.target.closest('.item-card');
                    if (!card) return;
                    const id = card.dataset.id;
                    const name = card.querySelector('.name')?.textContent;
                    const price = parseFloat(card.dataset.price) || 0;
                    const stock = parseInt(card.dataset.stock) || 0;

                    // Already added?
                    const existingItem = document.querySelector(
                        `#cart .cart-item[data-itemid="${id}"], #cart .sale-item[data-itemid="${id}"]`);
                    if (existingItem) {
                        showError('Already added! Use the +/- buttons in the cart to change quantity.');
                        return;
                    }
                    if (stock <= 0 && card.dataset.unlimited !== '1' && !(window.recipeProductIds || []).includes(parseInt(id, 10))) {
                        showError('Stock out!');
                        return;
                    }

                    window.dispatchEvent(new CustomEvent('open-item-modal'));

                    // populate modal
                    const modal = document.getElementById('itemModal');
                    if (modal) {
                        modal.querySelector('input[name="id"]').value = id;
                        window.modalProductId = id;
                        modal.querySelector('input[name="stock"]').value = stock;
                        modal.querySelector('input[name="quantity"]').value = 1;
                        modal.querySelector('input[name="price"]').value = price;
                        const basePrice = modal.querySelector('input[name="base_price"]');
                        if (basePrice) basePrice.value = price;
                        modal.querySelector('input[name="discount_amount"]').value = 0;
                        const title = document.getElementById('productModalLabel');
                        if (title) title.textContent = name;
                        renderModifiers(id);
                        renderExtras(id);
                        renderItemAddons(id);
                        recalcModalTotal();
                    }
                });

                window.recipeProductIds = @json(($recipeProductIds ?? collect())->values());
                const productModifiersMap = @json($productModifiersMap ?? []);
                const productAddonsMap = @json($productAddonsMap ?? []);
                const productExtrasMap = @json($productExtrasMap ?? []);
                const posVat = @json($vatConfig ?? ['mode' => 'disabled', 'rate' => 0]);

                function round2(n) {
                    return Math.round((Number(n) || 0) * 100) / 100;
                }

                // Mirrors App\Support\VatCalculator: exclusive adds VAT on top,
                // inclusive extracts the VAT portion already inside prices.
                function vatFor(subtotal, discount) {
                    const rate = Number(posVat.rate || 0);
                    const net = Math.max(0, (Number(subtotal) || 0) - (Number(discount) || 0));
                    if (posVat.mode === 'exclusive' && rate > 0) {
                        return { mode: 'exclusive', rate, vat: round2(net * rate / 100) };
                    }
                    if (posVat.mode === 'inclusive' && rate > 0) {
                        return { mode: 'inclusive', rate, vat: round2(net * rate / (100 + rate)) };
                    }
                    return { mode: 'disabled', rate: 0, vat: 0 };
                }
                const canManageAdditions = @json(auth()->user()->can('products'));

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

                function selectedAdditions() {
                    const modal = document.getElementById('itemModal');
                    if (!modal) return [];
                    return Array.from(modal.querySelectorAll('input[name="addition_ids[]"]:checked')).map(el => ({
                        id: parseInt(el.value, 10),
                        name: el.dataset.name,
                        price: parseFloat(el.dataset.price) || 0,
                    }));
                }

                function additionsExtra() {
                    return selectedAdditions().reduce((sum, a) => sum + (a.price || 0), 0);
                }

                function selectedItemAddons() {
                    const modal = document.getElementById('itemModal');
                    if (!modal) return [];
                    return Array.from(modal.querySelectorAll('input[name="addon_ids[]"]:checked')).map(el => ({
                        id: parseInt(el.value, 10),
                        name: el.dataset.name,
                        price: parseFloat(el.dataset.price) || 0,
                    }));
                }

                function itemAddonsExtra() {
                    return selectedItemAddons().reduce((sum, a) => sum + (a.price || 0), 0);
                }

                window.toggleQuickAddition = function(e, hide) {
                    if (e) e.stopPropagation();
                    const form = document.getElementById('quickAdditionForm');
                    if (!form) return;
                    form.classList.toggle('hidden', hide === true ? true : !form.classList.contains('hidden'));
                    if (!form.classList.contains('hidden')) {
                        document.getElementById('quickAdditionName')?.focus();
                    }
                };

                window.saveQuickAddition = function() {
                    const nameEl = document.getElementById('quickAdditionName');
                    const priceEl = document.getElementById('quickAdditionPrice');
                    const name = (nameEl?.value || '').trim();
                    const price = parseFloat(priceEl?.value || '0') || 0;
                    if (!name) {
                        showError('Enter an addition name.');
                        return;
                    }
                    fetch("{{ route('admin.additions.quick') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrf,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ name, price, product_id: Number(window.modalProductId || 0) || null })
                        })
                        .then(r => r.json().then(d => ({ ok: r.ok, d })))
                        .then(({ ok, d }) => {
                            if (!ok || !d.data?.addition) {
                                showError(d.message || 'Could not save addition.');
                                return;
                            }
                            const addition = d.data.addition;
                            const pid = String(window.modalProductId || '');
                            if (pid) {
                                if (!productExtrasMap[pid]) productExtrasMap[pid] = [];
                                productExtrasMap[pid].push(addition);
                            }
                            if (nameEl) nameEl.value = '';
                            if (priceEl) priceEl.value = 0;
                            window.toggleQuickAddition(null, true);
                            renderExtras(pid);
                            // Pre-tick the fresh addition.
                            const list = document.getElementById('extrasList');
                            const box = list?.querySelector(`input[name="addition_ids[]"][value="${addition.id}"]`);
                            if (box) box.checked = true;
                            recalcModalTotal();
                            window.toast?.success(addition.name + ' added to this item.');
                        })
                        .catch(err => showError(err.message || 'Network error'));
                };

                function renderExtras(productId) {
                    const section = document.getElementById('extrasSection');
                    const list = document.getElementById('extrasList');
                    if (!section || !list) return;
                    const extras = (productExtrasMap[productId] || []).filter(a => a && a.id);
                    if (!extras.length && !canManageAdditions) {
                        section.style.display = 'none';
                        list.innerHTML = '';
                        return;
                    }
                    section.style.display = '';
                    list.innerHTML = extras.map(a => `
                    <label class="cursor-pointer inline-block">
                        <input type="checkbox" class="hidden peer addition-check" name="addition_ids[]" value="${a.id}"
                               data-name="${String(a.name).replace(/"/g, '&quot;')}"
                               data-price="${a.price}">
                        <span class="addition-pill">+ ${a.name} · ৳${Number(a.price).toFixed(2)}</span>
                    </label>`).join('');
                    list.querySelectorAll('.addition-check').forEach(el => {
                        el.addEventListener('change', recalcModalTotal);
                    });
                }

                function renderItemAddons(productId) {
                    const section = document.getElementById('addonsSection');
                    const list = document.getElementById('addonsList');
                    if (!section || !list) return;
                    const addons = (productAddonsMap[productId] || []).filter(a => a && a.id);
                    if (!addons.length) {
                        section.style.display = 'none';
                        list.innerHTML = '';
                        return;
                    }
                    section.style.display = '';
                    list.innerHTML = addons.map(a => `
                    <label class="cursor-pointer inline-block">
                        <input type="checkbox" class="hidden peer addon-check" name="addon_ids[]" value="${a.id}"
                               data-name="${String(a.name).replace(/"/g, '&quot;')}"
                               data-price="${a.price}">
                        <span class="addon-pill">⊕ ${a.name} · ৳${Number(a.price).toFixed(2)}</span>
                    </label>`).join('');
                    list.querySelectorAll('.addon-check').forEach(el => {
                        el.addEventListener('change', recalcModalTotal);
                    });
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
                    <label class="flex items-center justify-between p-2 rounded-xl bg-white border border-slate-200 text-xs text-slate-700 cursor-pointer hover:border-orange-400 transition">
                        <div class="flex items-center gap-2">
                            <input type="checkbox" class="rounded border-slate-300 text-orange-600 focus:ring-orange-500 modifier-check"
                                   name="modifier_ids[]" value="${m.id}"
                                   data-name="${m.name.replace(/"/g, '&quot;')}"
                                   data-group="${(m.group_name || '').replace(/"/g, '&quot;')}"
                                   data-price="${m.price}"
                                   ${m.is_required ? 'checked' : ''}>
                            <span class="font-semibold text-slate-800">${m.name}</span>
                        </div>
                        <span class="text-orange-600 font-bold">+৳${m.price}</span>
                    </label>
                `).join('');
                        return `<div class="space-y-1.5"><div class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 px-1">${group}</div>${rows}</div>`;
                    }).join('');
                    list.querySelectorAll('.modifier-check').forEach(el => {
                        el.addEventListener('change', recalcModalTotal);
                    });
                }

                function recalcModalTotal() {                    const modal = document.getElementById('itemModal');
                    if (!modal) return;
                    const q = parseFloat(modal.querySelector('input[name="quantity"]').value) || 0;
                    const p = parseFloat(modal.querySelector('input[name="price"]').value) || 0;
                    const dtype = modal.querySelector('select[name="discount_type"]').value;
                    const damount = parseFloat(modal.querySelector('input[name="discount_amount"]').value) || 0;
                    const extra = modifiersExtra() + additionsExtra() + itemAddonsExtra();
                    let discount = dtype === 'amount' ? damount : ((p + extra) * damount / 100);
                    const total = (q * (p + extra)) - (discount || 0);
                    const t = modal.querySelector('#product-total-price');
                    if (t) t.textContent = isNaN(total) ? 0 : total.toFixed(2);
                }

                // --- Modal field listeners ---
                const modalEl = document.getElementById('itemModal');
                if (modalEl) {
                    modalEl.querySelectorAll(
                        'input[name="quantity"], input[name="price"], input[name="discount_amount"]').forEach(
                    el => {
                        el.addEventListener('keyup', recalcModalTotal);
                        el.addEventListener('input', recalcModalTotal);
                    });
                    modalEl.querySelector('select[name="discount_type"]').addEventListener('change', recalcModalTotal);
                }

                // --- Add to cart from modal ---
                window.addItem = function() {
                    const modal = document.getElementById('itemModal');
                    if (!modal) return;
                    const id = modal.querySelector('input[name="id"]').value;
                    const quantity = modal.querySelector('input[name="quantity"]').value;
                    const price = modal.querySelector('input[name="price"]').value;
                    const dtype = modal.querySelector('select[name="discount_type"]').value;
                    const damount = parseFloat(modal.querySelector('input[name="discount_amount"]').value) || 0;
                    const mods = selectedModifiers();
                    const adds = selectedAdditions();
                    const paddons = selectedItemAddons();
                    const extra = mods.reduce((s, m) => s + (m.price || 0), 0) + adds.reduce((s, a) => s + (a.price || 0), 0) + paddons.reduce((s, a) => s + (a.price || 0), 0);
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

                    const url = isSale ?
                        "{{ route('admin.pos.saleItem.add') }}" :
                        "{{ route('admin.pos.addItem') }}";
                    const oid = isSale ? saleOrderId : orderId;

                    fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrf,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                order_id: oid,
                                product_id: id,
                                quantity,
                                unit_price: price,
                                discount,
                                modifiers: mods,
                                additions: adds,
                                addons: paddons,
                            })
                        })
                        .then(r => r.json().then(d => ({
                            ok: r.ok,
                            d
                        })))
                        .then(({
                            ok,
                            d
                        }) => {
                            btn.innerHTML = original;
                            btn.disabled = false;
                            if (!ok) {
                                showError(d.message || 'Error');
                                return;
                            }
                            setItemToCart(d.data.item, d.data.cart_item_html);
                            window.dispatchEvent(new CustomEvent('close-item-modal'));
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
                                    modifiers: mods,
                                    addons: paddons,
                                };
                                const empty = $cart.querySelector('.empty-state');
                                if (empty) empty.remove();
                                const mainEl = offlineCartElement(line);
                                $cart.appendChild(mainEl);
                                updateCartTotals();
                                window.dispatchEvent(new CustomEvent('close-item-modal'));
                                window.toast?.warning(
                                    'Item added locally. It will be validated during synchronization.');
                                return;
                            }
                            showError(err.message || 'Network error');
                        });
                };

                // --- Remove item ---
                window.removeItem = function(cartItemId) {
                    const localItem = document.getElementById('cart-item-' + cartItemId);
                    if (!navigator.onLine || localItem?.dataset.source === 'offline') {
                        localItem?.remove();
                        updateCartTotals();
                        return;
                    }

                    const url = isSale ?
                        "{{ route('admin.pos.saleItem.remove') }}" :
                        "{{ route('admin.pos.removeItem') }}";
                    fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrf,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                [isSale ? 'sale_item_id' : 'cart_item_id']: cartItemId
                            })
                        })
                        .then(r => r.json())
                        .then(d => {
                            const sel = isSale ? '#sale-item-' : '#cart-item-';
                            const el = document.querySelector(sel + cartItemId);
                            if (el) el.remove();
                            const item = d.data?.item;
                            if (item && !item.unlimited) {
                                const stockEl = document.querySelector('#item-' + item.id + ' .stock');
                                if (stockEl) stockEl.textContent = item.stock;
                                const card = document.getElementById('item-' + item.id);
                                if (card) card.dataset.stock = item.stock;
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
                document.addEventListener('click', function(e) {
                    if (e.target.matches('.increment, .saleIncrement') || e.target.closest(
                            '.increment, .saleIncrement')) {
                        const btn = e.target.matches('.increment, .saleIncrement') ? e.target : e.target
                            .closest('.increment, .saleIncrement');
                        const wrap = btn.parentElement;
                        const input = wrap.querySelector('.quantityInput, .saleQuantityInput');
                        const cartItem = btn.closest('.cart-item, .sale-item');
                        const itemId = cartItem.dataset.itemid;
                        const card = document.getElementById('item-' + itemId);
                        if (card && card.dataset.unlimited === '1') {
                            input.value = parseInt(input.value) + 1;
                            if (isSale) updateSaleQuantity(cartItem.dataset.id);
                            else updateCartQuantity(cartItem.dataset.id);
                            return;
                        }
                        const stock = parseInt(document.querySelector('#item-' + itemId + ' .stock')
                            ?.textContent) || 0;
                        if (stock <= 0) {
                            const isRecipe = (window.recipeProductIds || []).includes(parseInt(itemId, 10));
                            if (!isRecipe) {
                                showError('Stock out!');
                                return;
                            }
                        }
                        input.value = parseInt(input.value) + 1;
                        if (isSale) updateSaleQuantity(cartItem.dataset.id);
                        else updateCartQuantity(cartItem.dataset.id);
                    }
                    if (e.target.matches('.decrement, .saleDecrement') || e.target.closest(
                            '.decrement, .saleDecrement')) {
                        const btn = e.target.matches('.decrement, .saleDecrement') ? e.target : e.target
                            .closest('.decrement, .saleDecrement');
                        const wrap = btn.parentElement;
                        const input = wrap.querySelector('.quantityInput, .saleQuantityInput');
                        if (parseInt(input.value) <= 1) return;
                        input.value = parseInt(input.value) - 1;
                        const cartItem = btn.closest('.cart-item, .sale-item');
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
                    fetch("{{ route('admin.pos.updateQuantity') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrf,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                cart_item_id: cartItemId,
                                quantity: qty
                            })
                        })
                        .then(r => r.json())
                        .then(d => {
                            const item = d.data.item;
                            if (item && !item.unlimited) {
                                const stockEl = document.querySelector('#item-' + item.id + ' .stock');
                                if (stockEl) stockEl.textContent = item.stock;
                                const card = document.getElementById('item-' + item.id);
                                if (card) card.dataset.stock = item.stock;
                            }
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
                    fetch("{{ route('admin.pos.saleItem.updateQuantity') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrf,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                sale_item_id: saleItemId,
                                quantity: qty
                            })
                        })
                        .then(r => r.json())
                        .then(d => {
                            const item = d.data.item;
                            if (item && !item.unlimited) {
                                const stockEl = document.querySelector('#item-' + item.id + ' .stock');
                                if (stockEl) stockEl.textContent = item.stock;
                                const card = document.getElementById('item-' + item.id);
                                if (card) card.dataset.stock = item.stock;
                            }
                            saleItem.querySelector('.price').textContent = d.data.sale_item.total_price;
                            updateCartTotals();
                        });
                }

                // --- Discount / Paid ---
                $discountInput.addEventListener('input', updateCheckoutPrice);
                $paidInput.addEventListener('input', updateCheckoutPrice);

                // --- Quick lookup: matches product code, exact name, then partial name ---
                window.handleBarcode = function(code) {
                    const term = (code || '').trim();
                    if (!term) return;
                    const needle = term.toLowerCase();
                    const cards = Array.from(document.querySelectorAll('.item-card'));
                    const nameOf = c => (c.querySelector('.name')?.textContent || '').trim().toLowerCase();

                    const card = cards.find(c => c.dataset.code && c.dataset.code === term) ||
                        cards.find(c => nameOf(c) === needle) ||
                        cards.find(c => nameOf(c).includes(needle));

                    if (card) {
                        card.click();
                        const input = document.getElementById('barcodeInput');
                        if (input) input.value = '';
                        window.dispatchEvent(new CustomEvent('close-barcode'));
                    } else {
                        showError('No product found for: ' + term);
                    }
                };

                document.getElementById('productCodeInput')?.addEventListener('keyup', function(e) {
                    if (e.key !== 'Enter') return;
                    window.handleBarcode(e.target.value);
                    e.target.value = '';
                });

                // --- Customer form toggle ---
                window.toggleCustomerForm = function() {
                    document.getElementById('customerForm')?.classList.toggle('hidden');
                };

                // --- Product name search ---
                document.getElementById('productNameSearch')?.addEventListener('keyup', function(e) {
                    const q = e.target.value.toLowerCase();
                    document.querySelectorAll('.item-card').forEach(card => {
                        const name = card.querySelector('.name')?.textContent.toLowerCase() || '';
                        card.style.display = name.includes(q) ? '' : 'none';
                    });
                });

                // Escape key clears search
                document.getElementById('productNameSearch')?.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape') {
                        this.value = '';
                        document.querySelectorAll('.item-card').forEach(card => card.style.display = '');
                    }
                });

                // --- Checkout ---
                window.checkout = async function() {
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
                    if (window.posOrderMode === 'dine_in' && !($tableSelect?.value)) {
                        btn.innerHTML = original;
                        btn.disabled = false;
                        showError('Select a table for dine-in order.');
                        return;
                    }
                    const requestPayload = {
                        order_id: orderId,
                        customer_id: $customerSelect?.value,
                        customer_name: $customerName?.value,
                        customer_phone: $customerPhone?.value,
                        order_type: window.posOrderMode,
                        table_id: window.posOrderMode === 'dine_in' ? ($tableSelect?.value || null) : null,
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
                        $cart.innerHTML = `<div class="empty-state py-12 px-4 text-center">
                    <div class="h-14 w-14 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center text-2xl mx-auto mb-3">
                        <i class="ri-cloud-off-line"></i>
                    </div>
                    <h3 class="text-sm font-bold text-slate-800">Order Saved Offline</h3>
                    <p class="text-xs text-slate-500 mt-1">Ref: <span class="font-mono font-bold">${clientOrderId.slice(0, 8).toUpperCase()}</span></p>
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
                        const response = await fetch("{{ route('admin.pos.checkout') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrf,
                                'Accept': 'application/json'
                            },
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

                        const url = "{{ route('admin.sales.invoice', $cart->order_id) }}";
                        window.open(url, 'Invoice', 'width=800,height=600,scrollbars=yes,resizable=yes');
                        setTimeout(() => window.location.reload(), 200);
                    } catch (error) {
                        try {
                            await saveOffline();
                        } catch (storageError) {
                            btn.innerHTML = original;
                            btn.disabled = false;
                            showError('Network failed and the order could not be saved: ' + storageError
                                .message);
                        }
                    }
                };

                // --- Hold ---
                window.hold = function() {
                    const btn = document.getElementById('holdBtn');
                    const original = btn.innerHTML;
                    btn.innerHTML = '<i class="ri-loader-4-line animate-spin"></i> Saving...';
                    btn.disabled = true;

                    fetch("{{ route('admin.pos.hold') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrf,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                order_id: orderId,
                                customer_id: $customerSelect?.value,
                                customer_name: $customerName?.value,
                                customer_phone: $customerPhone?.value,
                                order_type: window.posOrderMode,
                                table_id: window.posOrderMode === 'dine_in' ? ($tableSelect?.value || null) : null,
                                employee_id: $employeeSelect?.value,
                                discount_amount: $discountInput.value,
                                paid_amount: $paidInput.value,
                                note: $note.value,
                            })
                        })
                        .then(r => r.json().then(d => ({
                            ok: r.ok,
                            d
                        })))
                        .then(({
                            ok,
                            d
                        }) => {
                            if (!ok) {
                                btn.innerHTML = original;
                                btn.disabled = false;
                                showError(d.message || 'Error');
                                return;
                            }
                            setTimeout(() => window.location.reload(), 200);
                        })
                        .catch(err => {
                            btn.innerHTML = original;
                            btn.disabled = false;
                            showError(err.message || 'Network error');
                        });
                };

                // --- Update Sale ---
                window.updateSale = function() {
                    const btn = document.getElementById('updateSaleBtn');
                    const original = btn.innerHTML;
                    btn.innerHTML = '<i class="ri-loader-4-line animate-spin"></i> Updating...';
                    btn.disabled = true;

                    fetch("{{ route('admin.pos.updateSale') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrf,
                                'Accept': 'application/json'
                            },
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
                        .then(r => r.json().then(d => ({
                            ok: r.ok,
                            d
                        })))
                        .then(({
                            ok,
                            d
                        }) => {
                            if (!ok) {
                                btn.innerHTML = original;
                                btn.disabled = false;
                                showError(d.message || 'Error');
                                return;
                            }
                            const url =
                                "{{ $sale ?? null ? route('admin.sales.invoice', $sale->order_id) : '#' }}";
                            window.open(url, 'Invoice', 'width=800,height=600,scrollbars=yes,resizable=yes');
                            setTimeout(() => window.location.href = "{{ route('admin.pos.index') }}", 200);
                        })
                        .catch(err => {
                            btn.innerHTML = original;
                            btn.disabled = false;
                            showError(err.message || 'Network error');
                        });
                };

                // --- Fullscreen ---
                document.getElementById('fullscreen-btn')?.addEventListener('click', function() {
                    if (!document.fullscreenElement) document.documentElement.requestFullscreen();
                    else document.exitFullscreen();
                });

                // --- Refresh ---
                document.getElementById('refresh-btn')?.addEventListener('click', () => location.reload());

                // --- Kitchen ready badge (polled; no websockets) ---
                (function pollKitchenReady() {
                    const readyBadge = document.getElementById('posKitchenReadyBadge');

                    function setReadyCount(n) {
                        n = Math.max(0, n);
                        if (!readyBadge) return;
                        if (n > 0) {
                            readyBadge.textContent = String(n);
                            readyBadge.classList.remove('hidden');
                        } else {
                            readyBadge.classList.add('hidden');
                        }
                    }

                    async function refresh() {
                        try {
                            const res = await window.axios.get('/admin/kds/summary');
                            setReadyCount(res.data?.ready ?? 0);
                        } catch (e) {
                            /* offline: keep last known */ }
                    }

                    refresh();
                    setInterval(refresh, 10000);
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
