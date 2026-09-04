<div class="cart-item bg-white border border-slate-200 rounded-lg p-2.5 flex items-center gap-2.5 hover:border-slate-300 transition"
     data-id="{{ $item->id }}"
     id="cart-item-{{ $item->id }}"
     data-itemid="{{ $item->item_id }}"
     data-name="{{ $item->item->name }}"
     data-unit-price="{{ $item->unit_price }}"
     data-discount="{{ $item->discount ?? 0 }}"
     data-note="{{ $item->note }}"
     data-modifiers="{{ json_encode($item->modifiers_json ?? []) }}"
     data-source="server_cart">
    <img src="{{ $item->item->imageUrl() }}" alt="" class="h-11 w-11 object-cover rounded-md shrink-0 bg-orange-50">
    <div class="flex-1 min-w-0">
        <div class="text-sm font-medium text-slate-800 truncate">{{ $item->item->name }}</div>
        @if (!empty($item->modifiers_json))
            <div class="text-[10px] text-slate-500 truncate">
                {{ collect($item->modifiers_json)->pluck('name')->filter()->implode(', ') }}
            </div>
        @endif
        @if (!empty($item->note))
            <div class="text-[10px] text-amber-700 truncate">{{ $item->note }}</div>
        @endif
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
