<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slash Restora | Digital Menu</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">

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
</head>

<body
    x-data="{
        cart: [],
        cartOpen: false,
        confirmOpen: false,
        orderNumber: '',
        add(card) {
            const id = card.dataset.id;
            const existing = this.cart.find(i => i.id === id);
            if (existing) { existing.quantity++; } else {
                this.cart.push({
                    id, name: card.dataset.name,
                    price: parseFloat(card.dataset.price),
                    image: card.dataset.image,
                    quantity: 1
                });
            }
            this.updateCardUI(card, this.cart.find(i => i.id === id).quantity);
            this.updateCartUI();
        },
        updateQty(id, delta) {
            const item = this.cart.find(i => i.id === id);
            if (!item) return;
            item.quantity += delta;
            if (item.quantity <= 0) this.cart = this.cart.filter(i => i.id !== id);
            const card = document.querySelector(`.menu-card[data-id='${id}']`);
            if (card) this.updateCardUI(card, item.quantity > 0 ? item.quantity : 0);
            this.updateCartUI();
        },
        updateCardUI(card, qty) {
            const actions = card.querySelector('.item-actions');
            const available = card.dataset.available === '1';
            if (!available) {
                actions.innerHTML = `<span class='not-available-badge'><i class='ri-close-circle-line'></i> Not Available</span>`;
                return;
            }
            if (qty > 0) {
                actions.innerHTML = `
                    <div class='quantity-controls'>
                        <button type='button' class='qty-btn minus' data-act='minus' data-id='${card.dataset.id}'><i class='ri-subtract-line'></i></button>
                        <span class='qty-value'>${qty}</span>
                        <button type='button' class='qty-btn plus' data-act='plus' data-id='${card.dataset.id}'><i class='ri-add-line'></i></button>
                    </div>`;
            } else {
                actions.innerHTML = `<button type='button' class='add-btn' data-act='add'><i class='ri-add-line'></i></button>`;
            }
        },
        updateCartUI() {
            const totalItems = this.cart.reduce((s, i) => s + i.quantity, 0);
            const subtotal = this.cart.reduce((s, i) => s + i.price * i.quantity, 0);
            this.$refs.cartBarCount.textContent = totalItems;
            this.$refs.cartBarTotal.textContent = subtotal.toFixed(2);
            this.$refs.headerCartBadge.textContent = totalItems;
            this.$refs.headerCartBadge.classList.toggle('empty', totalItems === 0);
            this.$refs.cartBar.classList.toggle('show', totalItems > 0);
            if (this.cart.length === 0) {
                this.$refs.cartItems.innerHTML = `
                    <div class='empty-cart'>
                        <i class='ri-shopping-bag-line'></i>
                        <h5>Your cart is empty</h5>
                        <p class='text-gray-500'>Add items from the menu to get started</p>
                    </div>`;
                this.$refs.cartSummarySection.style.display = 'none';
            } else {
                let html = '';
                this.cart.forEach(item => {
                    html += `
                        <div class='cart-item' data-id='${item.id}'>
                            <img src='${item.image}' alt='${item.name}' class='cart-item-image'>
                            <div class='cart-item-details'>
                                <div class='cart-item-name'>${item.name}</div>
                                <div class='cart-item-price'>৳${(item.price * item.quantity).toFixed(2)}</div>
                            </div>
                            <div class='cart-item-controls'>
                                <div class='quantity-controls'>
                                    <button type='button' class='qty-btn minus' data-act='minus' data-id='${item.id}'><i class='ri-subtract-line'></i></button>
                                    <span class='qty-value'>${item.quantity}</span>
                                    <button type='button' class='qty-btn plus' data-act='plus' data-id='${item.id}'><i class='ri-add-line'></i></button>
                                </div>
                            </div>
                        </div>`;
                });
                this.$refs.cartItems.innerHTML = html;
                this.$refs.cartSummarySection.style.display = 'block';
                this.$refs.subtotal.textContent = subtotal.toFixed(2);
                this.$refs.total.textContent = subtotal.toFixed(2);
            }
        },
        scrollToCategory(id) {
            const sec = document.getElementById(id);
            if (sec) sec.scrollIntoView({ behavior: 'smooth', block: 'start' });
        },
        async placeOrder() {
            if (this.cart.length === 0) return;
            const btn = this.$refs.placeOrderBtn;
            btn.disabled = true;
            const original = btn.innerHTML;
            btn.innerHTML = 'Placing order...';
            try {
                const res = await fetch(`/menu/{{ $table->id }}/order`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ items: this.cart.map(i => ({ id: i.id, quantity: i.quantity })) })
                });
                if (!res.ok) throw new Error('Order failed');
                const data = await res.json();
                this.orderNumber = data.order_id;
                this.cartOpen = false;
                setTimeout(() => { this.confirmOpen = true; }, 300);
                this.cart = [];
                this.updateCartUI();
                document.querySelectorAll('.menu-card').forEach(c => this.updateCardUI(c, 0));
            } catch (err) {
                alert('Something went wrong. Please try again.');
            } finally {
                btn.disabled = false;
                btn.innerHTML = original;
            }
        }
    }"
    @click="const t = $event.target.closest('[data-act]'); if (!t) return;
             if (t.dataset.act === 'add') add(t.closest('.menu-card'));
             else if (t.dataset.act === 'plus' || t.dataset.act === 'minus') {
                const card = t.closest('.menu-card') || t.closest('.cart-item');
                if (card) updateQty(card.dataset.id, t.dataset.act === 'plus' ? 1 : -1);
             }"
    @keydown.escape.window="cartOpen = false; confirmOpen = false"
>
    <header class="header">
        <div class="max-w-5xl mx-auto px-4 py-3 flex items-center justify-between">
            <div class="brand-logo">Slash<span>.</span>Restora</div>
            <div class="flex items-center gap-3">
                <span class="table-badge">
                    <i class="ri-map-pin-line mr-1"></i> {{ $table->name ?? $table->id }}
                </span>
                <button type="button" class="cart-btn" @click="cartOpen = true" aria-label="Open cart">
                    <i class="ri-shopping-bag-line"></i>
                    <span class="cart-badge empty" x-ref="headerCartBadge">0</span>
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
                            <article class="menu-card"
                                     data-id="{{ $product->id }}"
                                     data-name="{{ $product->name }}"
                                     data-price="{{ $product->selling_price }}"
                                     data-image="{{ storage_url($product->image) }}"
                                     data-available="{{ $product->availableStock > 0 ? '1' : '0' }}">
                                <div class="menu-card-body">
                                    <img src="{{ storage_url($product->image) }}" alt="{{ $product->name }}" class="menu-card-image" loading="lazy">
                                    <div class="menu-card-content">
                                        <h3 class="menu-card-title">{{ $product->name }}</h3>
                                        <div class="menu-card-footer">
                                            <div class="menu-card-price">{{ money($product->selling_price) }}</div>
                                            <div class="item-actions">
                                                @if ($product->availableStock > 0)
                                                    <button type="button" class="add-btn" data-act="add">
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

    <div class="cart-bar" x-ref="cartBar" x-cloak>
        <div class="cart-bar-info">
            <span class="cart-bar-count"><span x-ref="cartBarCount">0</span> items</span>
            <span class="text-gray-400">|</span>
            <span class="cart-bar-total">৳<span x-ref="cartBarTotal">0.00</span></span>
        </div>
        <button type="button" class="view-cart-btn" @click="cartOpen = true">
            View Cart & Checkout
            <i class="ri-arrow-right-line ml-2"></i>
        </button>
    </div>

    <template x-teleport="body">
        <div x-show="cartOpen" x-cloak class="fixed inset-0 z-[60] flex items-end" style="display:none" @keydown.escape.window="cartOpen = false">
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
                    <div x-ref="cartItems">
                        <div class="empty-cart">
                            <i class="ri-shopping-bag-line"></i>
                            <h5>Your cart is empty</h5>
                            <p class="text-gray-500">Add items from the menu to get started</p>
                        </div>
                    </div>
                    <div x-ref="cartSummarySection" style="display:none;">
                        <div class="cart-summary">
                            <div class="cart-summary-row">
                                <span>Subtotal</span>
                                <span>৳<span x-ref="subtotal">0.00</span></span>
                            </div>
                            <div class="cart-summary-row total">
                                <span>Total</span>
                                <span>৳<span x-ref="total">0.00</span></span>
                            </div>
                        </div>
                        <button type="button" class="place-order-btn" x-ref="placeOrderBtn" @click="placeOrder()">
                            <i class="ri-send-plane-line mr-2"></i>Place Order
                        </button>
                        <p class="text-center text-gray-400 mt-3 text-xs">
                            <i class="ri-shield-check-line mr-1"></i>
                            Your order will be prepared immediately
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <template x-teleport="body">
        <div x-show="confirmOpen" x-cloak class="fixed inset-0 z-[70] flex items-center justify-center p-4" style="display:none" @keydown.escape.window="confirmOpen = false">
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
                    <strong>Estimated time: 15-20 minutes</strong>
                </p>
                <button type="button" class="done-btn" @click="confirmOpen = false">Done</button>
            </div>
        </div>
    </template>

    <style>
        [x-cloak] { display: none !important; }
    </style>
</body>

</html>
