<div class="item-card" id="item-{{ $item->id }}"
     data-id="{{ $item->id }}"
     data-code="{{ $item->item_code }}"
     data-category="{{ $item->category_id }}"
     data-price="{{ $item->selling_price }}"
     data-stock="{{ $item->availableStock }}">
    <div class="aspect-square bg-slate-50 overflow-hidden relative">
        <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}" class="w-full h-full object-cover">
        @if($item->availableStock <= 0)
            <div class="absolute inset-0 bg-slate-900/60 flex items-center justify-center">
                <span class="badge badge-danger">Stock out</span>
            </div>
        @endif
    </div>
    <div class="p-2.5">
        <h4 class="text-xs sm:text-sm font-medium text-slate-800 name line-clamp-1" title="{{ $item->name }}">{{ $item->name }}</h4>
        <div class="flex items-center justify-between mt-1">
            <span class="text-[10px] sm:text-xs text-slate-500">
                <span class="stock font-medium text-slate-700">{{ $item->availableStock }}</span> {{ $item->unit->short_name }}
            </span>
            <span class="text-xs sm:text-sm font-bold text-brand-600 price">{{ $item->selling_price }}</span>
        </div>
    </div>
</div>
