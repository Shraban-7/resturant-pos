<div x-data="{ open: false }" @keydown.escape.window="open = false" @open-item-modal.window="open = true"
    @close-item-modal.window="open = false">
    <template x-teleport="body">
        <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none">
            <div class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm" @click="open = false"></div>
            <div class="relative w-full max-w-lg bg-white rounded-3xl shadow-2xl border border-slate-200/80 max-h-[90vh] flex flex-col overflow-hidden"
                @click.stop>
                {{-- Header --}}
                <div
                    class="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-slate-50/60 shrink-0">
                    <div class="flex items-center gap-2.5">
                        <span
                            class="flex items-center justify-center h-9 w-9 rounded-xl bg-orange-600 text-white shadow-sm">
                            <i class="ri-restaurant-2-line text-lg"></i>
                        </span>
                        <div>
                            <h3 class="text-base font-bold text-slate-900 leading-tight" id="productModalLabel">
                                Customize Item</h3>
                            <p class="text-[11px] text-slate-500 font-medium">Configure quantity, add-ons & special
                                requests</p>
                        </div>
                    </div>
                    <button type="button"
                        class="h-8 w-8 rounded-full hover:bg-slate-200 text-slate-400 hover:text-slate-700 flex items-center justify-center transition"
                        @click="open = false" aria-label="Close">
                        <i class="ri-close-line text-xl"></i>
                    </button>
                </div>

                {{-- Form Fields --}}
                <div class="px-6 py-5 space-y-4 overflow-y-auto" id="itemModal">
                    <input type="hidden" name="id" value="">
                    <input type="hidden" name="stock" value="">
                    <input type="hidden" name="base_price" value="">

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Quantity</label>
                            <input type="number"
                                class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm font-bold focus:outline-none focus:ring-2 focus:ring-orange-500 bg-slate-50/50"
                                name="quantity" value="1" min="1" step="1">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Unit Rate (৳)</label>
                            <input type="number"
                                class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm font-bold focus:outline-none focus:ring-2 focus:ring-orange-500 bg-slate-50/50"
                                name="price" step="0.01">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Discount Type</label>
                            <select name="discount_type"
                                class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-orange-500 bg-white">
                                <option value="amount">Fixed (৳)</option>
                                <option value="percentage">Percentage (%)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Discount Amount</label>
                            <input type="number"
                                class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm font-bold focus:outline-none focus:ring-2 focus:ring-orange-500 bg-slate-50/50"
                                name="discount_amount" value="0" min="0" step="0.01">
                        </div>

                        {{-- Modifiers & Add-ons --}}
                        <div class="col-span-2" id="modifiersSection" style="display:none">
                            <label
                                class="block text-xs font-bold text-slate-700 mb-1.5 flex items-center justify-between">
                                <span>Modifiers & Add-ons</span>
                                <span class="text-[11px] font-normal text-slate-400">Select options</span>
                            </label>
                            <div id="modifiersList"
                                class="space-y-2 max-h-44 overflow-y-auto border border-slate-200 rounded-2xl p-3 bg-slate-50/80">
                            </div>
                        </div>

                        {{-- Special Note --}}
                        <div class="col-span-2">
                            <label class="block text-xs font-bold text-slate-700 mb-1">Special Instructions</label>
                            <div class="relative">
                                <i class="ri-edit-line absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                <input type="text"
                                    class="w-full border border-slate-200 rounded-xl pl-9 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500"
                                    name="note" placeholder="e.g. Less spicy, extra sauce, allergy note...">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Footer Summary & Action --}}
                <div
                    class="flex items-center justify-between px-6 py-4 border-t border-slate-100 bg-slate-50 rounded-b-3xl shrink-0">
                    <div>
                        <span class="text-[11px] uppercase tracking-wider font-bold text-slate-400 block">Item
                            Total</span>
                        <div class="flex items-baseline gap-1">
                            <span class="text-sm font-bold text-orange-600">৳</span>
                            <span class="text-2xl font-extrabold text-slate-900 tracking-tight"
                                id="product-total-price">0</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" id="addToCartBtn"
                            class="bg-orange-600 hover:bg-orange-700 text-white font-bold px-6 py-2.5 rounded-xl text-sm transition shadow-lg shadow-orange-600/20 active:scale-95 flex items-center gap-2"
                            onclick="window.addItem()">
                            <i class="ri-shopping-cart-2-line"></i> Add to Order
                        </button>
                    </div>
                </div>
                <p class="text-red-600 text-xs px-6 pb-2" id="modalErrorMsg"></p>
            </div>
        </div>
    </template>
</div>
