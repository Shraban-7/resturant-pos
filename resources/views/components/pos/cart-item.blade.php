<div class="cart-item bg-white border border-slate-200/80 rounded-xl p-2.5 flex items-center gap-3 hover:border-slate-300 hover:shadow-sm transition-all"
    data-id="{{ $item->id }}" id="cart-item-{{ $item->id }}" data-itemid="{{ $item->item_id }}"
    data-name="{{ $item->item->name }}" data-unit-price="{{ $item->unit_price }}"
    data-discount="{{ $item->discount ?? 0 }}" data-note="{{ $item->note }}"
    data-modifiers="{{ json_encode($item->modifiers_json ?? []) }}" data-source="server_cart">

    <img src="{{ $item->item->imageUrl() }}" alt=""
        class="h-12 w-12 object-cover rounded-lg shrink-0 bg-slate-100 border border-slate-100">

    <div class="flex-1 min-w-0">
        <div class="text-xs sm:text-sm font-bold text-slate-800 truncate leading-snug">{{ $item->item->name }}</div>

        @if (!empty($item->modifiers_json))
            <div
                class="text-[10px] text-orange-700 bg-orange-50 px-1.5 py-0.5 rounded inline-block truncate max-w-full mt-0.5 font-medium">
                + {{ collect($item->modifiers_json)->pluck('name')->filter()->implode(', ') }}
            </div>
        @endif

        @if (!empty($item->note))
            <div class="text-[10px] text-slate-500 italic truncate flex items-center gap-1 mt-0.5">
                <i class="ri-chat-1-line text-[11px]"></i> {{ $item->note }}
            </div>
        @endif

        <div class="flex items-center gap-1 mt-1.5">
            <button type="button"
                class="qty-btn decrement inline-flex items-center justify-center h-6 w-6 rounded-md border border-slate-200 bg-slate-50 text-slate-700 hover:bg-slate-200 active:scale-95 transition"
                aria-label="Decrease">
                <i class="ri-subtract-line text-xs pointer-events-none"></i>
            </button>
            <input type="text"
                class="quantityInput qty-input h-6 w-8 text-center text-xs font-bold text-slate-800 border-0 bg-transparent focus:ring-0 p-0"
                value="{{ $item->quantity }}" min="1" readonly>
            <button type="button"
                class="qty-btn increment inline-flex items-center justify-center h-6 w-6 rounded-md border border-slate-200 bg-slate-50 text-slate-700 hover:bg-slate-200 active:scale-95 transition"
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
