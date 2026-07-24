<div class="item-card bg-white border border-slate-200 rounded-xl overflow-hidden cursor-pointer transition hover:border-brand-400 hover:shadow-md active:scale-[0.98] flex flex-col justify-between h-full w-full" id="item-{{ $item->id }}"
     data-id="{{ $item->id }}"
     data-code="{{ $item->item_code ?? '' }}"
     data-category="{{ $item->category_id }}"
     data-price="{{ $item->selling_price }}"
     data-stock="{{ $item->availableStock }}">
    <div class="h-20 sm:h-22 lg:h-24 w-full bg-slate-50 overflow-hidden relative shrink-0">
        <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}" class="w-full h-full object-cover">
        @if($item->availableStock <= 0)
            <div class="absolute inset-0 bg-slate-900/60 flex items-center justify-center">
                <span class="badge badge-danger text-[10px] px-1.5 py-0.5 font-semibold uppercase">Stock out</span>
            </div>
        @endif
    </div>
    <div class="p-2 flex-1 flex flex-col justify-between min-h-[48px]">
        <h4 class="text-xs font-semibold text-slate-800 name line-clamp-1 leading-tight" title="{{ $item->name }}">{{ $item->name }}</h4>
        <div class="flex items-center justify-between mt-1 pt-1 border-t border-slate-100">
            <span class="text-[10px] text-slate-500 truncate">
                <span class="stock font-medium text-slate-700">{{ $item->availableStock }}</span> {{ $item->unit?->short_name ?? 'pcs' }}
            </span>
            <span class="text-xs font-bold text-brand-600 price shrink-0">৳{{ number_format($item->selling_price, 2) }}</span>
        </div>
    </div>
</div>
