<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Order Storefront - Restaurant POS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-slate-50 text-slate-800 font-sans min-h-screen flex flex-col" x-data="onlineOrderApp()">
    
    {{-- Navbar --}}
    <header class="bg-white border-b border-slate-200 sticky top-0 z-30">
        <div class="max-w-6xl mx-auto px-4 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-xl bg-indigo-600 flex items-center justify-center text-white font-bold text-xl">
                    <i class="ri-shopping-bag-3-line"></i>
                </div>
                <div>
                    <h1 class="font-bold text-slate-900 leading-tight">Gourmet Express</h1>
                    <p class="text-xs text-slate-500">Online Pickup & Delivery Portal</p>
                </div>
            </div>

            <button type="button" class="relative bg-indigo-50 border border-indigo-200 text-indigo-700 px-4 py-2 rounded-xl text-sm font-semibold flex items-center gap-2 hover:bg-indigo-100 transition" @click="cartOpen = true">
                <i class="ri-shopping-cart-2-line text-lg"></i>
                <span>My Cart</span>
                <span class="bg-indigo-600 text-white text-xs px-2 py-0.5 rounded-full font-bold" x-text="cartCount()">0</span>
            </button>
        </div>
    </header>

    {{-- Menu Grid --}}
    <main class="max-w-6xl mx-auto px-4 py-8 flex-1 w-full">
        @foreach ($categories as $category)
            @if ($category->products->count() > 0)
                <section class="mb-10">
                    <h2 class="text-lg font-bold text-slate-900 border-b border-slate-200 pb-2 mb-4 flex items-center gap-2">
                        <i class="ri-restaurant-line text-indigo-600"></i>
                        <span>{{ $category->name }}</span>
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach ($category->products as $p)
                            <div class="bg-white border border-slate-200 rounded-2xl p-4 flex gap-4 hover:shadow-md transition">
                                <div class="h-20 w-20 rounded-xl bg-slate-100 overflow-hidden shrink-0">
                                    <img src="{{ asset('storage/' . $p->image) }}" alt="{{ $p->name }}" class="h-full w-full object-cover">
                                </div>
                                <div class="flex-1 flex flex-col justify-between">
                                    <div>
                                        <h3 class="font-bold text-slate-900 text-sm line-clamp-1">{{ $p->name }}</h3>
                                        <p class="text-xs font-semibold text-indigo-600 mt-0.5">৳{{ number_format($p->selling_price, 2) }}</p>
                                    </div>
                                    <button class="bg-indigo-600 text-white text-xs font-semibold px-3 py-1.5 rounded-lg hover:bg-indigo-700 transition self-start flex items-center gap-1 mt-2"
                                            @click="addToCart({{ $p->id }}, '{{ addslashes($p->name) }}', {{ $p->selling_price }})">
                                        <i class="ri-add-line"></i> Add
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif
        @endforeach
    </main>

    {{-- Cart Drawer --}}
    <div x-show="cartOpen" class="fixed inset-0 z-50 overflow-hidden" style="display:none">
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" @click="cartOpen = false"></div>
        <div class="absolute inset-y-0 right-0 max-w-full flex pl-10">
            <div class="w-screen max-w-md bg-white shadow-2xl flex flex-col justify-between">
                <div class="p-4 border-b border-slate-200 flex items-center justify-between">
                    <h2 class="text-lg font-bold text-slate-900">Your Online Order</h2>
                    <button class="text-slate-400 hover:text-slate-600" @click="cartOpen = false"><i class="ri-close-line text-2xl"></i></button>
                </div>

                <div class="p-4 flex-1 overflow-y-auto space-y-3">
                    <template x-for="item in cart" :key="item.id">
                        <div class="flex items-center justify-between p-3 border border-slate-200 rounded-xl">
                            <div>
                                <h4 class="font-semibold text-slate-900 text-sm" x-text="item.name"></h4>
                                <p class="text-xs text-indigo-600 font-bold" x-text="'৳' + item.price.toFixed(2)"></p>
                            </div>
                            <div class="flex items-center gap-2">
                                <button class="h-7 w-7 rounded bg-slate-100 flex items-center justify-center font-bold text-slate-700" @click="updateQty(item.id, -1)">-</button>
                                <span class="text-sm font-bold w-6 text-center" x-text="item.qty"></span>
                                <button class="h-7 w-7 rounded bg-slate-100 flex items-center justify-center font-bold text-slate-700" @click="updateQty(item.id, 1)">+</button>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="p-4 border-t border-slate-200 space-y-3 bg-slate-50">
                    <div class="space-y-2">
                        <input type="text" x-model="customerName" placeholder="Your Full Name" class="w-full px-3 py-2 border rounded-lg text-xs">
                        <input type="text" x-model="customerPhone" placeholder="Phone Number" class="w-full px-3 py-2 border rounded-lg text-xs">
                        <textarea x-model="deliveryAddress" placeholder="Delivery Address" class="w-full px-3 py-2 border rounded-lg text-xs" rows="2"></textarea>
                        <select x-model="orderType" class="w-full px-3 py-2 border rounded-lg text-xs">
                            <option value="delivery">Home Delivery (+৳50)</option>
                            <option value="pickup">Self Pickup (৳0)</option>
                        </select>
                    </div>

                    <div class="flex justify-between font-bold text-base pt-2">
                        <span>Total Payable</span>
                        <span class="text-indigo-600" x-text="'৳' + totalAmount().toFixed(2)">৳0.00</span>
                    </div>

                    <button class="w-full bg-indigo-600 text-white font-bold py-3 rounded-xl hover:bg-indigo-700 transition" @click="submitOrder()">
                        Place Online Order
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function onlineOrderApp() {
            return {
                cartOpen: false,
                cart: [],
                customerName: '',
                customerPhone: '',
                deliveryAddress: '',
                orderType: 'delivery',
                addToCart(id, name, price) {
                    const existing = this.cart.find(i => i.id === id);
                    if (existing) { existing.qty++; }
                    else { this.cart.push({ id, name, price: Number(price), qty: 1 }); }
                },
                updateQty(id, diff) {
                    const existing = this.cart.find(i => i.id === id);
                    if (existing) {
                        existing.qty += diff;
                        if (existing.qty <= 0) {
                            this.cart = this.cart.filter(i => i.id !== id);
                        }
                    }
                },
                cartCount() { return this.cart.reduce((sum, i) => sum + i.qty, 0); },
                totalAmount() {
                    const subtotal = this.cart.reduce((sum, i) => sum + (i.price * i.qty), 0);
                    return subtotal + (this.orderType === 'delivery' ? 50 : 0);
                },
                async submitOrder() {
                    if (!this.customerName || !this.customerPhone || (this.orderType === 'delivery' && !this.deliveryAddress)) {
                        alert('Please fill in your contact and address details.');
                        return;
                    }
                    if (this.cart.length === 0) {
                        alert('Your cart is empty.');
                        return;
                    }
                    const res = await fetch('/online-order/checkout', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            customer_name: this.customerName,
                            customer_phone: this.customerPhone,
                            delivery_address: this.deliveryAddress,
                            order_type: this.orderType,
                            items: this.cart.map(i => ({ id: i.id, quantity: i.qty }))
                        })
                    });
                    const data = await res.json();
                    if (data.status) {
                        alert('Order placed successfully! Order ID: ' + data.data.order_id);
                        window.location.href = '/order-status/' + data.data.order_id;
                    } else {
                        alert(data.message || 'Failed to place order');
                    }
                }
            };
        }
    </script>
</body>
</html>
