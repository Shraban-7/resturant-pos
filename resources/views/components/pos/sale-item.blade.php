<div class="sale-item bg-white border border-amber-200/80 rounded-xl p-2.5 flex items-center gap-3 hover:border-amber-300 hover:shadow-sm transition-all"
    data-id="{{ $item->id }}" id="sale-item-{{ $item->id }}" data-itemid="{{ $item->item_id }}"
    data-name="{{ $item->item_name }}" data-unit="{{ $item->unit }}" data-unit-price="{{ $item->unit_price }}">

    <img src="{{ $item->product?->imageUrl() ?? asset('storage/' . $item->product?->image) }}" alt=""
        class="h-12 w-12 object-cover rounded-lg shrink-0 bg-slate-100 border border-slate-100">

    <div class="flex-1 min-w-0">
        <div class="text-xs sm:text-sm font-bold text-slate-800 truncate leading-snug">{{ $item->product->name }}</div>
        <div class="text-[10px] text-amber-700 font-medium mt-0.5">Editing Existing Item</div>

        <div class="flex items-center gap-1 mt-1.5">
            <button type="button"
                class="qty-btn saleDecrement inline-flex items-center justify-center h-6 w-6 rounded-md border border-slate-200 bg-slate-50 text-slate-700 hover:bg-slate-200 active:scale-95 transition"
                aria-label="Decrease">
                <i class="ri-subtract-line text-xs pointer-events-none"></i>
            </button>
            <input type="text"
                class="saleQuantityInput qty-input h-6 w-8 text-center text-xs font-bold text-slate-800 border-0 bg-transparent focus:ring-0 p-0"
                value="{{ $item->quantity }}" min="1" readonly>
            <button type="button"
                class="qty-btn saleIncrement inline-flex items-center justify-center h-6 w-6 rounded-md border border-slate-200 bg-slate-50 text-slate-700 hover:bg-slate-200 active:scale-95 transition"
                aria-label="Increase">
                <i class="ri-add-line text-xs pointer-events-none"></i>
            </button>
        </div>
    </div>

    <div class="text-right shrink-0 flex flex-col items-end justify-between self-stretch">
        <div class="text-xs sm:text-sm font-extrabold text-slate-900 tracking-tight flex items-baseline gap-0.5">
            <span class="text-[10px] font-normal text-slate-400">৳</span>
            <span class="price">{{ $item->total_price }}</span>
        </div>
        <button type="button" class="text-slate-400 hover:text-red-600 transition p-1 rounded hover:bg-red-50"
            title="Remove item" onclick="window.removeItem('{{ $item->id }}')">
            <i class="ri-delete-bin-line text-sm"></i>
        </button>
    </div>
</div>
