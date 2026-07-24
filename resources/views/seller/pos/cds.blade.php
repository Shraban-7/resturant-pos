<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Display Screen - Restaurant POS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-slate-900 text-white font-sans h-screen flex flex-col justify-between overflow-hidden" x-data="cdsApp()">
    
    {{-- Header --}}
    <header class="bg-slate-800/80 border-b border-slate-700/60 p-6 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="h-10 w-10 rounded-xl bg-brand-600 flex items-center justify-center text-white font-bold text-xl">
                <i class="ri-restaurant-2-line"></i>
            </div>
            <div>
                <h1 class="text-xl font-bold tracking-tight">Welcome to Our Restaurant</h1>
                <p class="text-xs text-slate-400">Customer Display Order Summary</p>
            </div>
        </div>
        <div class="flex items-center gap-2 text-xs font-semibold px-3 py-1.5 rounded-full bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
            <span class="h-2 w-2 rounded-full bg-emerald-400 animate-ping"></span>
            <span>Live POS Sync</span>
        </div>
    </header>

    {{-- Main Content --}}
    <main class="flex-1 flex overflow-hidden p-6 gap-6">
        {{-- Left: Item List --}}
        <div class="flex-1 bg-slate-800/50 border border-slate-700/60 rounded-2xl flex flex-col overflow-hidden">
            <div class="p-4 border-b border-slate-700/60 bg-slate-800/80 flex justify-between text-xs font-semibold text-slate-400 uppercase tracking-wider">
                <span>Item Description</span>
                <div class="flex gap-12">
                    <span class="w-16 text-center">Qty</span>
                    <span class="w-24 text-right">Price</span>
                    <span class="w-28 text-right">Total</span>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto p-4 space-y-3">
                <template x-for="item in cartItems" :key="item.id">
                    <div class="flex items-center justify-between p-3.5 bg-slate-800/80 border border-slate-700/40 rounded-xl">
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 rounded-lg bg-slate-700 flex items-center justify-center text-slate-300 font-semibold text-sm">
                                <i class="ri-cup-line"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold text-sm text-slate-100" x-text="item.name"></h3>
                                <p class="text-xs text-slate-400" x-text="item.unit ? 'Unit: ' + item.unit : ''"></p>
                            </div>
                        </div>

                        <div class="flex items-center gap-12 text-sm">
                            <span class="w-16 text-center font-semibold text-brand-400 bg-brand-500/10 px-2 py-1 rounded-md" x-text="'x' + item.quantity"></span>
                            <span class="w-24 text-right text-slate-300" x-text="'৳' + Number(item.unit_price).toFixed(2)"></span>
                            <span class="w-28 text-right font-bold text-white text-base" x-text="'৳' + Number(item.total_price).toFixed(2)"></span>
                        </div>
                    </div>
                </template>

                <template x-if="cartItems.length === 0">
                    <div class="h-full flex flex-col items-center justify-center text-slate-500 gap-3 py-16">
                        <i class="ri-shopping-cart-2-line text-6xl text-slate-600"></i>
                        <p class="text-base font-medium">Ready for your order...</p>
                    </div>
                </template>
            </div>
        </div>

        {{-- Right: Total Summary Card --}}
        <div class="w-96 bg-gradient-to-br from-slate-800 to-slate-900 border border-slate-700/80 rounded-2xl p-6 flex flex-col justify-between shadow-2xl">
            <div>
                <h2 class="text-lg font-bold border-b border-slate-700/60 pb-3 mb-6 flex items-center gap-2">
                    <i class="ri-bill-line text-brand-400"></i>
                    <span>Order Summary</span>
                </h2>

                <div class="space-y-4 text-sm text-slate-300">
                    <div class="flex justify-between">
                        <span class="text-slate-400">Subtotal</span>
                        <span class="font-semibold text-white" x-text="'৳' + subtotal.toFixed(2)">৳0.00</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Discount</span>
                        <span class="font-semibold text-emerald-400" x-text="'-৳' + discount.toFixed(2)">-৳0.00</span>
                    </div>
                    <div class="border-t border-slate-700/60 pt-4 flex justify-between items-baseline">
                        <span class="text-base font-medium text-slate-300">Total Payable</span>
                        <span class="text-3xl font-extrabold text-brand-400" x-text="'৳' + totalPayable.toFixed(2)">৳0.00</span>
                    </div>
                </div>
            </div>

            <div class="bg-slate-800/90 rounded-xl p-4 border border-slate-700/50 text-center">
                <p class="text-xs text-slate-400">Thank you for dining with us!</p>
                <p class="text-xs font-semibold text-brand-400 mt-1">Please inspect your items before payment.</p>
            </div>
        </div>
    </main>

    <script>
        function cdsApp() {
            return {
                cartItems: [],
                subtotal: 0,
                discount: 0,
                totalPayable: 0,
                init() {
                    const channel = new BroadcastChannel('cds_cart_sync');
                    channel.onmessage = (event) => {
                        if (event.data?.type === 'CART_STATE') {
                            this.cartItems = event.data.items || [];
                            this.subtotal = Number(event.data.subtotal || 0);
                            this.discount = Number(event.data.discount || 0);
                            this.totalPayable = Number(event.data.totalPayable || 0);
                        }
                    };
                    channel.postMessage({ type: 'REQUEST_STATE' });
                }
            };
        }
    </script>
</body>
</html>
