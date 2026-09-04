<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Slash Restora | Digital Menu</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>
    <style>
        :root {
            --menu-primary: #1a1a2e;
            --menu-primary-light: #16213e;
            --menu-accent: #e94560;
            --menu-accent-hover: #d63d56;
            --menu-gold: #c9a962;
        }

        * { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #fafafa;
            color: #1a1a2e;
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
            padding-bottom: 100px;
        }

        .header {
            background: #fff;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .brand-logo {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1a1a2e;
            letter-spacing: -0.5px;
        }
        .brand-logo span { color: #c9a962; }

        .table-badge {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            color: white;
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.375rem 0.75rem;
            border-radius: 9999px;
            letter-spacing: 0.5px;
        }

        .cart-btn {
            position: relative;
            width: 44px;
            height: 44px;
            border-radius: 9999px;
            background: #fafafa;
            border: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            color: #1a1a2e;
        }
        .cart-btn:hover, .cart-btn:focus {
            background: #1a1a2e;
            color: #fff;
        }
        .cart-btn i { font-size: 1.25rem; }
        .cart-badge {
            position: absolute;
            top: -2px;
            right: -2px;
            min-width: 20px;
            height: 20px;
            background: #e94560;
            color: #fff;
            font-size: 0.65rem;
            font-weight: 700;
            border-radius: 9999px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid #fff;
            padding: 0 4px;
        }
        .cart-badge.empty { display: none; }

        .category-section {
            background: #fff;
            position: sticky;
            top: 68px;
            z-index: 40;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }
        .category-scroll {
            display: flex;
            gap: 0.5rem;
            overflow-x: auto;
            padding: 1rem 0;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }
        .category-scroll::-webkit-scrollbar { display: none; }

        .category-tab {
            flex-shrink: 0;
            padding: 0.625rem 1.25rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 500;
            color: #6c757d;
            background: #fafafa;
            border: 0;
            cursor: pointer;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            white-space: nowrap;
        }
        .category-tab:hover { color: #1a1a2e; background: #f0f0f0; }
        .category-tab.active { background: #1a1a2e; color: #fff; }

        .menu-section { padding: 1.5rem 0; }
        .section-title {
            font-size: 1.125rem;
            font-weight: 700;
            color: #1a1a2e;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .section-title::after {
            content: '';
            flex: 1;
            height: 1px;
            background: linear-gradient(to right, rgba(0, 0, 0, 0.1), transparent);
        }

        .menu-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            overflow: hidden;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            margin-bottom: 1rem;
            border: 1px solid rgba(0, 0, 0, 0.04);
        }
        .menu-card:hover {
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            transform: translateY(-2px);
        }

        .menu-card-body {
            display: flex;
            gap: 1rem;
            padding: 1rem;
        }
        .menu-card-image {
            width: 100px;
            height: 100px;
            border-radius: 12px;
            object-fit: cover;
            flex-shrink: 0;
        }
        .menu-card-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
        }
        .menu-card-title {
            font-size: 1rem;
            font-weight: 600;
            color: #1a1a2e;
            margin: 0 0 0.25rem;
        }
        .menu-card-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: auto;
        }
        .menu-card-price {
            font-size: 1.125rem;
            font-weight: 700;
            color: #1a1a2e;
        }

        .add-btn {
            width: 36px;
            height: 36px;
            border-radius: 9999px;
            background: #e94560;
            color: #fff;
            border: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            font-size: 1.25rem;
        }
        .add-btn:hover {
            background: #d63d56;
            transform: scale(1.1);
        }

        .not-available-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.4rem 0.75rem;
            font-size: 0.75rem;
            font-weight: 600;
            color: #842029;
            background: #f8d7da;
            border-radius: 9999px;
            white-space: nowrap;
        }

        .quantity-controls {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: #fafafa;
            border-radius: 9999px;
            padding: 0.25rem;
        }
        .qty-btn {
            width: 32px;
            height: 32px;
            border-radius: 9999px;
            border: 0;
            background: #fff;
            color: #1a1a2e;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        }
        .qty-btn:hover { background: #1a1a2e; color: #fff; }
        .qty-value {
            min-width: 24px;
            text-align: center;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .cart-bar {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: #fff;
            box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.1);
            padding: 1rem;
            z-index: 60;
            transform: translateY(100%);
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 24px 24px 0 0;
        }
        .cart-bar.show { transform: translateY(0); }
        .cart-bar-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 0.75rem;
        }
        .cart-bar-count {
            background: #1a1a2e;
            color: #fff;
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.375rem 0.75rem;
            border-radius: 9999px;
        }
        .cart-bar-total {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1a1a2e;
        }
        .view-cart-btn {
            padding: 0.875rem 2rem;
            font-size: 1rem;
            font-weight: 600;
            border-radius: 9999px;
            background: #e94560;
            color: #fff;
            border: 0;
            width: 100%;
            cursor: pointer;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .view-cart-btn:hover { background: #d63d56; }

        .cart-item {
            display: flex;
            gap: 1rem;
            padding: 1rem 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }
        .cart-item:last-child { border-bottom: 0; }
        .cart-item-image {
            width: 64px;
            height: 64px;
            border-radius: 8px;
            object-fit: cover;
        }
        .cart-item-details { flex: 1; }
        .cart-item-name { font-weight: 600; font-size: 0.95rem; margin-bottom: 0.25rem; }
        .cart-item-price { color: #6c757d; font-size: 0.875rem; }
        .cart-item-controls { display: flex; align-items: center; }

        .cart-summary {
            background: #fafafa;
            border-radius: 12px;
            padding: 1rem;
            margin-top: 1rem;
        }
        .cart-summary-row {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            font-size: 0.95rem;
        }
        .cart-summary-row.total {
            border-top: 1px solid rgba(0, 0, 0, 0.1);
            margin-top: 0.5rem;
            padding-top: 0.75rem;
            font-weight: 700;
            font-size: 1.125rem;
        }
        .place-order-btn {
            width: 100%;
            padding: 1rem;
            font-size: 1rem;
            font-weight: 600;
            border-radius: 12px;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            color: #fff;
            border: 0;
            margin-top: 1rem;
            cursor: pointer;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .place-order-btn:hover { opacity: 0.9; transform: translateY(-1px); }

        .empty-cart {
            text-align: center;
            padding: 3rem 1rem;
        }
        .empty-cart i { font-size: 4rem; color: #adb5bd; margin-bottom: 1rem; }
        .empty-cart h5 { color: #6c757d; font-weight: 500; }

        .confirmation-title { font-size: 1.5rem; font-weight: 700; margin: 0 0 0.5rem; text-align: center; }
        .order-number {
            background: #fafafa;
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            display: inline-block;
            margin: 1rem 0;
        }
        .order-number span { font-weight: 700; color: #1a1a2e; font-size: 1.25rem; }
        .confirmation-message { color: #6c757d; margin-bottom: 1.5rem; text-align: center; }
        .done-btn {
            padding: 0.875rem 2.5rem;
            font-weight: 600;
            border-radius: 9999px;
            background: #1a1a2e;
            color: #fff;
            border: 0;
            cursor: pointer;
            display: block;
            margin: 0 auto;
        }

        @media (min-width: 768px) {
            .menu-card-body { padding: 1.25rem; }
            .menu-card-image { width: 120px; height: 120px; }
            .menu-card-title { font-size: 1.125rem; }
            .cart-bar {
                left: 50%;
                transform: translateX(-50%) translateY(100%);
                max-width: 500px;
                border-radius: 24px;
                bottom: 1rem;
            }
            .cart-bar.show { transform: translateX(-50%) translateY(0); }
        }
        @media (min-width: 992px) {
            body { padding-bottom: 120px; }
            .menu-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; }
            .menu-card { margin-bottom: 0; }
        }
    </style>
    <script>
        // Toast fallback (this page does not load app.js). Mirrors resources/js/toast.js styling.
        (function () {
            if (window.toast) return;
            function ensureContainer() {
                let el = document.getElementById('toast-container');
                if (!el) {
                    el = document.createElement('div');
                    el.id = 'toast-container';
                    el.style.cssText = 'position:fixed;top:1rem;right:1rem;z-index:9999;display:flex;flex-direction:column;gap:.5rem;max-width:min(92vw,24rem);';
                    document.body.appendChild(el);
                }
                return el;
            }
            function show(message, type) {
                const colors = { success: '#059669', error: '#dc2626', warning: '#d97706', info: '#0284c7' };
                const icons = { success: '✓', error: '✕', warning: '!', info: 'i' };
                const box = ensureContainer();
                while (box.children.length >= 4) box.firstElementChild?.remove();
                const el = document.createElement('div');
                el.style.cssText = 'display:flex;align-items:center;gap:.6rem;background:#fff;color:#0f172a;border-radius:.75rem;box-shadow:0 12px 30px rgba(0,0,0,.18);padding:.7rem .8rem .7rem .9rem;font-size:.85rem;border:1px solid #e2e8f0;border-left:4px solid ' + (colors[type] || colors.info) + ';animation:toastIn .25s ease both;';
                const badge = document.createElement('span');
                badge.textContent = icons[type] || 'i';
                badge.style.cssText = 'flex:none;display:flex;align-items:center;justify-content:center;height:1.75rem;width:1.75rem;border-radius:9999px;font-weight:700;font-size:.8rem;color:#fff;background:' + (colors[type] || colors.info) + ';';
                const txt = document.createElement('span');
                txt.style.cssText = 'flex:1;line-height:1.4;';
                txt.textContent = message;
                const btn = document.createElement('button');
                btn.textContent = '×';
                btn.style.cssText = 'font-size:1.1rem;line-height:1;opacity:.5;';
                btn.onclick = () => el.remove();
                el.appendChild(badge);
                el.appendChild(txt);
                el.appendChild(btn);
                box.appendChild(el);
                setTimeout(() => el.remove(), 4000);
            }
            window.toast = {
                success: (m) => show(m, 'success'),
                error: (m) => show(m, 'error'),
                warning: (m) => show(m, 'warning'),
                info: (m) => show(m, 'info'),
            };
        })();
    </script>
</head>

<body x-data="digitalMenuApp(@js($productModifiersMap ?? []))" x-cloak
    @keydown.escape.window="cartOpen = false; confirmOpen = false; modifierOpen = false">
    <header class="header">
        <div class="max-w-5xl mx-auto px-4 py-3 flex items-center justify-between">
            <div class="brand-logo">Slash<span>.</span>Restora</div>
            <div class="flex items-center gap-3">
                <span class="table-badge">
                    <i class="ri-map-pin-line mr-1"></i> {{ $table->name ?? $table->id }}
                </span>
                <button type="button" class="cart-btn" @click="cartOpen = true" aria-label="Open cart">
                    <i class="ri-shopping-bag-line"></i>
                    <span class="cart-badge" :class="{ empty: cartCount === 0 }" x-text="cartCount"></span>
                </button>
            </div>
        </div>
    </header>

    <section class="category-section">
        <div class="max-w-5xl mx-auto px-4">
            <div class="category-scroll">
                @foreach($categories as $category)
                    <button type="button" class="category-tab {{ $loop->first ? 'active' : '' }}"
                            data-category="{{ $category->id }}"
                            @click="scrollToCategory('{{ $category->id }}')">
                        {{ $category->name }}
                    </button>
                @endforeach
            </div>
        </div>
    </section>

    <main class="menu-section">
        <div class="max-w-5xl mx-auto px-4">
            @foreach ($categories as $category)
                <section id="{{ $category->id }}" class="menu-category mb-6">
                    <h2 class="section-title">{{ $category->name }}</h2>
                    <div class="menu-grid">
                        @foreach ($category->products as $product)
                            @php
                                $hasMods = ($productModifiersMap[$product->id] ?? []) !== [];
                                $inStock = $product->availableStock > 0;
                            @endphp
                            <article class="menu-card"
                                     data-id="{{ $product->id }}"
                                     data-name="{{ $product->name }}"
                                     data-price="{{ $product->selling_price }}"
                                     data-image="{{ storage_url($product->image) }}"
                                     data-available="{{ $inStock ? '1' : '0' }}"
                                     data-has-modifiers="{{ $hasMods ? '1' : '0' }}">
                                <div class="menu-card-body">
                                    <img src="{{ storage_url($product->image) }}" alt="{{ $product->name }}" class="menu-card-image" loading="lazy">
                                    <div class="menu-card-content">
                                        <h3 class="menu-card-title">{{ $product->name }}</h3>
                                        @if($hasMods)
                                            <div class="text-xs text-gray-400 mb-1">Customizable</div>
                                        @endif
                                        <div class="menu-card-footer">
                                            <div class="menu-card-price">{{ money($product->selling_price) }}</div>
                                            <div class="item-actions">
                                                @if ($inStock)
                                                    <button type="button" class="add-btn" @click="startAdd($el.closest('.menu-card'))">
                                                        <i class="ri-add-line"></i>
                                                    </button>
                                                @else
                                                    <span class="not-available-badge">
                                                        <i class="ri-close-circle-line"></i> Not Available
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>
    </main>

    <div class="cart-bar" :class="{ show: cartCount > 0 }" x-cloak>
        <div class="cart-bar-info">
            <span class="cart-bar-count"><span x-text="cartCount">0</span> items</span>
            <span class="text-gray-400">|</span>
            <span class="cart-bar-total">৳<span x-text="cartTotal.toFixed(2)">0.00</span></span>
        </div>
        <button type="button" class="view-cart-btn" @click="cartOpen = true">
            View Cart & Checkout
            <i class="ri-arrow-right-line ml-2"></i>
        </button>
    </div>

    <template x-teleport="body">
        <div x-show="modifierOpen" x-cloak class="fixed inset-0 z-[65] flex items-end sm:items-center justify-center p-0 sm:p-4" style="display:none">
            <div class="absolute inset-0 bg-gray-900/50" @click="modifierOpen = false"></div>
            <div class="relative w-full sm:max-w-md bg-white rounded-t-3xl sm:rounded-3xl shadow-2xl max-h-[85vh] flex flex-col">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h5 class="text-lg font-bold text-gray-900" x-text="pendingItem?.name"></h5>
                    <p class="text-sm text-gray-500">Choose options</p>
                </div>
                <div class="flex-1 overflow-y-auto px-5 py-3 space-y-4">
                    <template x-for="group in modifierGroups" :key="group.name">
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-2">
                                <span x-text="group.name"></span>
                                <span x-show="group.required" class="text-rose-500 normal-case"> · required</span>
                            </div>
                            <div class="space-y-2">
                                <template x-for="mod in group.items" :key="mod.id">
                                    <label class="flex items-center gap-3 p-3 rounded-xl border border-gray-100 hover:border-gray-300 cursor-pointer">
                                        <input type="checkbox" class="rounded border-gray-300"
                                               :checked="isModSelected(mod.id)"
                                               @change="toggleModifier(mod)">
                                        <span class="flex-1 text-sm font-medium" x-text="mod.name"></span>
                                        <span class="text-sm text-gray-500" x-text="mod.price > 0 ? ('+৳' + Number(mod.price).toFixed(2)) : 'Free'"></span>
                                    </label>
                                </template>
                            </div>
                        </div>
                    </template>
                    <div>
                        <label class="text-xs font-semibold uppercase tracking-wide text-gray-500">Special notes</label>
                        <textarea class="mt-2 w-full rounded-xl border border-gray-200 px-3 py-2 text-sm" rows="2"
                                  x-model="pendingNote" placeholder="e.g. less spicy, no onion"></textarea>
                    </div>
                </div>
                <div class="p-4 border-t border-gray-100 flex gap-2">
                    <button type="button" class="flex-1 py-3 rounded-xl border border-gray-200 font-semibold" @click="modifierOpen = false">Cancel</button>
                    <button type="button" class="flex-1 py-3 rounded-xl text-white font-semibold" style="background:#e94560" @click="confirmModifiers()">
                        Add · ৳<span x-text="pendingLineTotal.toFixed(2)"></span>
                    </button>
                </div>
            </div>
        </div>
    </template>

    <template x-teleport="body">
        <div x-show="cartOpen" x-cloak class="fixed inset-0 z-[60] flex items-end" style="display:none">
            <div class="absolute inset-0 bg-gray-900/50" @click="cartOpen = false"></div>
            <div class="relative w-full bg-white rounded-t-3xl shadow-2xl flex flex-col" style="max-height: 80vh;">
                <div class="w-10 h-1 bg-gray-300 rounded mx-auto mt-3"></div>
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <h5 class="text-lg font-bold text-gray-900">
                        <i class="ri-shopping-bag-3-line mr-2"></i>Your Order
                    </h5>
                    <button type="button" class="text-gray-400 hover:text-gray-700" @click="cartOpen = false" aria-label="Close">
                        <i class="ri-close-line text-2xl"></i>
                    </button>
                </div>
                <div class="flex-1 px-6 py-2 overflow-y-auto">
                    <template x-if="cart.length === 0">
                        <div class="empty-cart">
                            <i class="ri-shopping-bag-line"></i>
                            <h5>Your cart is empty</h5>
                            <p class="text-gray-500">Add items from the menu to get started</p>
                        </div>
                    </template>
                    <template x-if="cart.length > 0">
                        <div>
                            <template x-for="item in cart" :key="item.key">
                                <div class="cart-item">
                                    <img :src="item.image" :alt="item.name" class="cart-item-image">
                                    <div class="cart-item-details">
                                        <div class="cart-item-name" x-text="item.name"></div>
                                        <div class="text-xs text-gray-400" x-show="item.modifiers?.length"
                                             x-text="(item.modifiers || []).map(m => m.name).join(', ')"></div>
                                        <div class="text-xs text-amber-700 italic" x-show="item.note" x-text="item.note"></div>
                                        <div class="cart-item-price">৳<span x-text="(item.price * item.quantity).toFixed(2)"></span></div>
                                    </div>
                                    <div class="cart-item-controls">
                                        <div class="quantity-controls">
                                            <button type="button" class="qty-btn minus" @click="updateQty(item.key, -1)"><i class="ri-subtract-line"></i></button>
                                            <span class="qty-value" x-text="item.quantity"></span>
                                            <button type="button" class="qty-btn plus" @click="updateQty(item.key, 1)"><i class="ri-add-line"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </template>
                            <div class="cart-summary">
                                <div class="cart-summary-row">
                                    <span>Subtotal</span>
                                    <span>৳<span x-text="cartTotal.toFixed(2)"></span></span>
                                </div>
                                <div class="cart-summary-row total">
                                    <span>Total</span>
                                    <span>৳<span x-text="cartTotal.toFixed(2)"></span></span>
                                </div>
                            </div>
                            <button type="button" class="place-order-btn" :disabled="placing" @click="placeOrder()">
                                <i class="ri-send-plane-line mr-2"></i>
                                <span x-text="placing ? 'Placing order...' : 'Place Order'"></span>
                            </button>
                            <p class="text-center text-gray-400 mt-3 text-xs">
                                <i class="ri-shield-check-line mr-1"></i>
                                Your order will be prepared immediately
                            </p>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </template>

    <template x-teleport="body">
        <div x-show="confirmOpen" x-cloak class="fixed inset-0 z-[70] flex items-center justify-center p-4" style="display:none">
            <div class="absolute inset-0 bg-gray-900/60" @click="confirmOpen = false"></div>
            <div class="relative bg-white rounded-3xl shadow-2xl p-8 max-w-sm w-full text-center">
                <div class="w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6"
                     style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
                    <i class="ri-check-line text-white" style="font-size: 2.5rem;"></i>
                </div>
                <h2 class="confirmation-title">Order Placed!</h2>
                <div class="order-number">
                    Order #<span x-text="orderNumber"></span>
                </div>
                <p class="confirmation-message">
                    Your order has been sent to the kitchen.<br>
                    <strong>Track live preparation progress</strong>
                </p>
                <a :href="trackerUrl" class="done-btn inline-flex items-center justify-center no-underline mb-3" x-show="trackerUrl">
                    Track Order
                </a>
                <button type="button" class="done-btn" style="background:#6c757d" @click="confirmOpen = false">Done</button>
            </div>
        </div>
    </template>

    <style>
        [x-cloak] { display: none !important; }
    </style>

    <script>
        function digitalMenuApp(modifiersMap) {
            return {
                cart: [],
                cartOpen: false,
                confirmOpen: false,
                modifierOpen: false,
                placing: false,
                orderNumber: '',
                trackerUrl: '',
                modifiersMap: modifiersMap || {},
                pendingItem: null,
                pendingModifiers: [],
                pendingNote: '',

                get cartCount() {
                    return this.cart.reduce((s, i) => s + i.quantity, 0);
                },
                get cartTotal() {
                    return this.cart.reduce((s, i) => s + i.price * i.quantity, 0);
                },
                get modifierGroups() {
                    if (!this.pendingItem) return [];
                    const mods = this.modifiersMap[this.pendingItem.id] || [];
                    const groups = {};
                    mods.forEach(m => {
                        const name = m.group_name || 'Options';
                        if (!groups[name]) groups[name] = { name, required: false, items: [] };
                        groups[name].items.push(m);
                        if (m.is_required) groups[name].required = true;
                    });
                    return Object.values(groups);
                },
                get pendingLineTotal() {
                    if (!this.pendingItem) return 0;
                    const addons = this.pendingModifiers.reduce((s, m) => s + Number(m.price || 0), 0);
                    return Number(this.pendingItem.price) + addons;
                },

                lineKey(id, modifiers) {
                    const ids = (modifiers || []).map(m => m.id).sort((a, b) => a - b).join('-');
                    return id + ':' + ids;
                },
                isModSelected(id) {
                    return this.pendingModifiers.some(m => m.id === id);
                },
                toggleModifier(mod) {
                    if (this.isModSelected(mod.id)) {
                        this.pendingModifiers = this.pendingModifiers.filter(m => m.id !== mod.id);
                    } else {
                        this.pendingModifiers = [...this.pendingModifiers, mod];
                    }
                },
                startAdd(card) {
                    if (!card || card.dataset.available !== '1') return;
                    const id = card.dataset.id;
                    const base = {
                        id,
                        name: card.dataset.name,
                        price: parseFloat(card.dataset.price),
                        image: card.dataset.image,
                    };
                    if (card.dataset.hasModifiers === '1') {
                        this.pendingItem = base;
                        this.pendingModifiers = [];
                        this.pendingNote = '';
                        this.modifierOpen = true;
                        return;
                    }
                    this.addLine({ ...base, modifiers: [], note: '' });
                },
                confirmModifiers() {
                    for (const group of this.modifierGroups) {
                        if (group.required && !group.items.some(m => this.isModSelected(m.id))) {
                            window.toast?.warning('Please choose an option for: ' + group.name);
                            return;
                        }
                    }
                    this.addLine({
                        ...this.pendingItem,
                        modifiers: this.pendingModifiers.map(m => ({
                            id: m.id,
                            name: m.name,
                            price: Number(m.price),
                            group_name: m.group_name,
                        })),
                        note: this.pendingNote,
                        price: this.pendingLineTotal,
                    });
                    this.modifierOpen = false;
                    this.pendingItem = null;
                },
                addLine(line) {
                    const key = this.lineKey(line.id, line.modifiers);
                    const existing = this.cart.find(i => i.key === key);
                    if (existing) {
                        existing.quantity++;
                    } else {
                        this.cart.push({ ...line, key, quantity: 1 });
                    }
                },
                updateQty(key, delta) {
                    const item = this.cart.find(i => i.key === key);
                    if (!item) return;
                    item.quantity += delta;
                    if (item.quantity <= 0) this.cart = this.cart.filter(i => i.key !== key);
                },
                scrollToCategory(id) {
                    const sec = document.getElementById(id);
                    if (sec) sec.scrollIntoView({ behavior: 'smooth', block: 'start' });
                },
                async placeOrder() {
                    if (this.cart.length === 0 || this.placing) return;
                    this.placing = true;
                    try {
                        const res = await fetch(`/menu/{{ $table->id }}/order`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            },
                            body: JSON.stringify({
                                items: this.cart.map(i => ({
                                    id: Number(i.id),
                                    quantity: i.quantity,
                                    note: i.note || null,
                                    modifiers: i.modifiers || [],
                                })),
                            }),
                        });
                        const data = await res.json();
                        if (!res.ok || data.status === false) {
                            throw new Error(data.message || 'Order failed');
                        }
                        this.orderNumber = data.order_id;
                        this.trackerUrl = data.tracker_url || '';
                        this.cartOpen = false;
                        this.cart = [];
                        setTimeout(() => { this.confirmOpen = true; }, 250);
                    } catch (err) {
                        window.toast?.error(err.message || 'Something went wrong. Please try again.');
                    } finally {
                        this.placing = false;
                    }
                },
            };
        }
    </script>
</body>

</html>
