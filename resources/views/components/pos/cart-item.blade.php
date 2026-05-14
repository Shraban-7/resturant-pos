<div class="cart-item bg-white border border-slate-200 rounded-lg p-2.5 flex items-center gap-2.5 hover:border-slate-300 transition"
     data-id="{{ $item->id }}" id="cart-item-{{ $item->id }}" data-itemid="{{ $item->item_id }}">
    <img src="{{ asset('storage/' . $item->item->image) }}" alt="" class="h-11 w-11 object-cover rounded-md shrink-0">
    <div class="flex-1 min-w-0">
        <div class="text-sm font-medium text-slate-800 truncate">{{ $item->item->name }}</div>
        <div class="flex items-center gap-1 mt-1">
            <button type="button" class="qty-btn decrement" aria-label="Decrease">
                <i class="ri-subtract-line text-sm"></i>
            </button>
            <input type="text" class="quantityInput qty-input" value="{{ $item->quantity }}" min="1" readonly>
            <button type="button" class="qty-btn increment" aria-label="Increase">
                <i class="ri-add-line text-sm"></i>
            </button>
        </div>
    </div>
    <div class="text-right shrink-0">
        <div class="text-sm font-semibold text-slate-900 price">{{ $item->total_price }}</div>
        <button type="button" class="text-xs text-red-600 hover:underline mt-0.5" onclick="window.removeItem('{{ $item->id }}')">Remove</button>
    </div>
</div>
