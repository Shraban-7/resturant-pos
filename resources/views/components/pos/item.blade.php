<div class="item-card group bg-white border border-slate-200/80 rounded-2xl overflow-hidden cursor-pointer transition hover:border-brand-400 hover:shadow-lg hover:shadow-brand-600/10 hover:-translate-y-0.5 active:translate-y-0 active:scale-[0.98] flex flex-col h-full w-full" id="item-{{ $item->id }}"
     data-id="{{ $item->id }}"
     data-code="{{ $item->item_code ?? '' }}"
     data-category="{{ $item->category_id }}"
     data-price="{{ $item->selling_price }}"
     data-stock="{{ $item->availableStock }}">
    <div class="h-20 sm:h-24 w-full bg-slate-100 overflow-hidden relative shrink-0">
        <img src="{{ $item->imageUrl() }}" alt="{{ $item->displayName() }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300" loading="lazy">
        @if($item->availableStock <= 0)
            <div class="absolute inset-0 bg-slate-950/60 flex items-center justify-center">
                <span class="badge badge-danger text-[10px] px-2 py-0.5 font-bold uppercase tracking-wide">Stock out</span>
            </div>
        @endif
        <span class="absolute top-1.5 right-1.5 rounded-full bg-slate-950/70 backdrop-blur px-2 py-0.5 text-[11px] font-bold text-white price">৳{{ number_format($item->selling_price, $item->selling_price == (int) $item->selling_price ? 0 : 2) }}</span>
    </div>
    <div class="p-2 flex-1 flex flex-col justify-between gap-1 min-h-[52px]">
        <h4 class="text-xs font-semibold text-slate-800 name line-clamp-1 leading-tight" title="{{ $item->displayName() }}">{{ $item->displayName() }}</h4>
        <div class="flex items-center justify-between">
            <span class="text-[10px] text-slate-500 truncate">
                <span class="stock inline-flex items-center gap-1 font-medium {{ $item->availableStock > 0 ? 'text-emerald-700' : 'text-red-600' }}"><span class="h-1.5 w-1.5 rounded-full {{ $item->availableStock > 0 ? 'bg-emerald-500' : 'bg-red-500' }}"></span>{{ $item->availableStock }}</span>
                {{ $item->unit?->short_name ?? 'pcs' }}
            </span>
            <span class="inline-flex items-center justify-center h-6 w-6 rounded-full bg-brand-50 text-brand-600 text-sm font-bold group-hover:bg-brand-600 group-hover:text-white transition">+</span>
        </div>
    </div>
</div>
