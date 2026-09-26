<style>
    /* Multiselect pills (plain CSS so selection state works without a frontend rebuild) */
    .addition-pill, .addon-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.375rem 0.75rem;
        border-radius: 9999px;
        border: 1px solid #e2e8f0;
        background: #fff;
        color: #475569;
        font-size: 0.75rem;
        font-weight: 700;
        transition: background 0.15s, color 0.15s, border-color 0.15s, box-shadow 0.15s;
    }
    .addition-pill:hover { border-color: #6ee7b7; }
    .addon-pill:hover { border-color: #7dd3fc; }
    .addition-check:checked + .addition-pill {
        background: #059669;
        color: #fff;
        border-color: #059669;
        box-shadow: 0 4px 10px -2px rgb(5 150 105 / 0.35);
    }
    .addon-check:checked + .addon-pill {
        background: #0284c7;
        color: #fff;
        border-color: #0284c7;
        box-shadow: 0 4px 10px -2px rgb(2 132 199 / 0.35);
    }
</style>
<div x-data="{ open: false }" @keydown.escape.window="open = false" @open-item-modal.window="open = true"
    @close-item-modal.window="open = false">
    <template x-teleport="body">
        <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none">
            <div class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm" @click="open = false"></div>
            <div class="relative w-full max-w-lg bg-white rounded-3xl shadow-2xl border border-slate-200/80 max-h-[90vh] flex flex-col overflow-hidden"
                @click.stop>
                {{-- Header --}}
                <div
                    class="flex items-center justify-between px-4 py-2.5 border-b border-slate-100 bg-slate-50/60 shrink-0">
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
                <div class="px-4 py-3 space-y-3 overflow-y-auto" id="itemModal">
                    <input type="hidden" name="id" value="">
                    <input type="hidden" name="stock" value="">
                    <input type="hidden" name="base_price" value="">

                    <div class="grid grid-cols-2 gap-2.5">
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

                        {{-- Additions (extra options, priced) --}}
                        <div class="col-span-2" id="extrasSection" style="display:none">
                            <label
                                class="block text-xs font-bold text-slate-700 mb-1.5 flex items-center justify-between">
                                <span>Additions</span>
                                <span class="flex items-center gap-2">
                                    <span class="text-[11px] font-normal text-slate-400">Tick to add</span>
                                    @can('products')
                                        <button type="button" onclick="window.toggleQuickAddition(event)"
                                            class="inline-flex items-center gap-0.5 text-[11px] font-bold text-emerald-700 hover:text-emerald-800"
                                            title="Create a new addition on the fly">
                                            <i class="ri-add-circle-line text-sm"></i> New
                                        </button>
                                    @endcan
                                </span>
                            </label>
                            <div id="extrasList" class="flex flex-wrap gap-2"></div>
                            @can('products')
                                <div id="quickAdditionForm" class="hidden mt-2 p-2.5 rounded-xl bg-emerald-50/70 border border-emerald-200 space-y-2">
                                    <div class="grid grid-cols-2 gap-2">
                                        <input type="text" id="quickAdditionName" placeholder="Name, e.g. Cheese"
                                            class="w-full border border-slate-200 rounded-xl px-3 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500 bg-white">
                                        <div class="relative">
                                            <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs font-bold">৳</span>
                                            <input type="number" id="quickAdditionPrice" placeholder="Price" min="0" step="0.01" value="0"
                                                class="w-full border border-slate-200 rounded-xl pl-7 pr-2 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500 bg-white">
                                        </div>
                                    </div>
                                    <div class="flex justify-end gap-2">
                                        <button type="button" onclick="window.toggleQuickAddition(event, true)"
                                            class="px-3 py-1.5 rounded-xl text-xs font-bold text-slate-500 hover:bg-slate-100">Cancel</button>
                                        <button type="button" onclick="window.saveQuickAddition()"
                                            class="px-3 py-1.5 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white">Save Addition</button>
                                    </div>
                                </div>
                            @endcan
                        </div>

                        {{-- Suggested add-ons (multiselect, priced) --}}
                        <div class="col-span-2" id="addonsSection" style="display:none">
                            <label
                                class="block text-xs font-bold text-slate-700 mb-1.5 flex items-center justify-between">
                                <span>Suggested Add-ons</span>
                                <span class="text-[11px] font-normal text-slate-400">Tick to add — price folds into this item</span>
                            </label>
                            <div id="addonsList" class="flex flex-wrap gap-2"></div>
                        </div>
                    </div>
                </div>

                {{-- Footer Summary & Action --}}
                <div
                    class="flex items-center justify-between px-4 py-2.5 border-t border-slate-100 bg-slate-50 rounded-b-3xl shrink-0">
                    <div>
                        <span class="text-[11px] uppercase tracking-wider font-bold text-slate-400 block">Item
                            Total</span>
                        <div class="flex items-baseline gap-1">
                            <span class="text-sm font-bold text-orange-600">৳</span>
                            <span class="text-xl font-extrabold text-slate-900 tracking-tight"
                                id="product-total-price">0</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" id="addToCartBtn"
                            class="bg-orange-600 hover:bg-orange-700 text-white font-bold px-4 py-2 rounded-lg text-sm transition shadow-lg shadow-orange-600/20 active:scale-95 flex items-center gap-2"
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
