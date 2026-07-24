<div x-data="{ open: false }" @keydown.escape.window="open = false">
    <template x-teleport="body">
        <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none">
            <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" @click="open = false"></div>
            <div class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl border border-slate-200 max-h-[90vh] flex flex-col" @click.stop>
                <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100 shrink-0">
                    <h3 class="text-base font-semibold text-slate-900 flex items-center gap-2">
                        <i class="ri-edit-box-line text-brand-600"></i>
                        <span id="productModalLabel">Add to Cart</span>
                    </h3>
                    <button type="button" class="btn btn-ghost btn-icon" @click="open = false" aria-label="Close">
                        <i class="ri-close-line text-xl"></i>
                    </button>
                </div>
                <div class="px-5 py-4 space-y-3 overflow-y-auto" id="itemModal">
                    <input type="hidden" name="id" value="">
                    <input type="hidden" name="stock" value="">
                    <input type="hidden" name="base_price" value="">

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="form-label">Quantity</label>
                            <input type="number" class="form-control" name="quantity" value="1" min="1" step="1">
                        </div>
                        <div>
                            <label class="form-label">Rate</label>
                            <input type="number" class="form-control" name="price" step="0.01">
                        </div>
                        <div>
                            <label class="form-label">Discount Type</label>
                            <select name="discount_type" class="form-select">
                                <option value="amount">Amount</option>
                                <option value="percentage">Percentage (%)</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Discount Amount</label>
                            <input type="number" class="form-control" name="discount_amount" value="0" min="0" step="0.01">
                        </div>
                        <div class="col-span-2" id="modifiersSection" style="display:none">
                            <label class="form-label">Modifiers / Add-ons</label>
                            <div id="modifiersList" class="space-y-2 max-h-40 overflow-y-auto border border-slate-200 rounded-lg p-2 bg-slate-50"></div>
                            <p class="text-xs text-slate-500 mt-1">Selected add-ons are added to the line price.</p>
                        </div>
                        <div class="col-span-2">
                            <label class="form-label">Special instructions</label>
                            <input type="text" class="form-control" name="note" placeholder="e.g. No onions, medium rare...">
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-between px-5 py-4 border-t border-slate-100 bg-slate-50/50 rounded-b-2xl shrink-0">
                    <div>
                        <span class="text-xs text-slate-500 block">Total</span>
                        <span class="text-xl font-bold text-slate-900" id="product-total-price">0</span>
                    </div>
                    <div>
                        <button type="button" id="addToCartBtn" class="btn btn-primary" onclick="window.addItem()">
                            <i class="ri-shopping-cart-2-line"></i> Add to Cart
                        </button>
                        <p class="text-red-600 text-xs mt-1" id="modalErrorMsg"></p>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>
