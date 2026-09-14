<div class="item-card group relative bg-white border border-slate-200/90 rounded-2xl overflow-hidden cursor-pointer transition-all duration-200 hover:border-orange-400 hover:shadow-lg hover:shadow-orange-500/10 hover:-translate-y-0.5 active:translate-y-0 active:scale-[0.98] flex flex-col h-full w-full select-none"
    id="item-{{ $item->id }}" data-id="{{ $item->id }}" data-code="{{ $item->item_code ?? '' }}"
    data-category="{{ $item->category_id }}" data-price="{{ $item->selling_price }}"
    data-stock="{{ $item->availableStock }}">

    {{-- Product Image & Floating Price --}}
    <div class="h-24 sm:h-28 w-full bg-slate-100 overflow-hidden relative shrink-0">
        <img src="{{ $item->imageUrl() }}" alt="{{ $item->displayName() }}"
            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" loading="lazy">

        {{-- Stock Out Overlay --}}
        @if ($item->availableStock <= 0)
            <div class="absolute inset-0 bg-slate-950/70 backdrop-blur-[2px] flex items-center justify-center p-2">
                <span
                    class="inline-flex items-center gap-1 bg-red-600/90 text-white text-[10px] font-extrabold px-2 py-0.5 rounded-full uppercase tracking-wider shadow">
                    <i class="ri-close-circle-line"></i> Sold Out
                </span>
            </div>
        @endif

        {{-- Price Badge --}}
        <div
            class="absolute bottom-2 left-2 rounded-lg bg-slate-950/80 backdrop-blur-md px-2 py-0.5 text-white shadow-sm flex items-baseline gap-0.5">
            <span class="text-[10px] text-amber-300 font-bold">৳</span>
            <span
                class="text-xs font-extrabold tracking-tight">{{ number_format($item->selling_price, $item->selling_price == (int) $item->selling_price ? 0 : 2) }}</span>
        </div>
    </div>

    {{-- Card Content --}}
    <div class="p-2.5 flex-1 flex flex-col justify-between gap-1.5 min-h-[58px]">
        <div>
            <h4 class="text-xs font-bold text-slate-800 name line-clamp-1 leading-snug group-hover:text-orange-600 transition-colors"
                title="{{ $item->displayName() }}">
                {{ $item->displayName() }}
            </h4>
            @if (!empty($item->name_bn) && app()->getLocale() !== 'bn')
                <p class="text-[10px] text-slate-400 truncate leading-none mt-0.5">{{ $item->name_bn }}</p>
            @endif
        </div>

        <div class="flex items-center justify-between pt-1 border-t border-slate-100/80">
            <div class="flex items-center gap-1.5 text-[10px] font-medium text-slate-500">
                <span
                    class="flex h-1.5 w-1.5 rounded-full {{ $item->availableStock > 0 ? 'bg-emerald-500' : 'bg-red-500' }}"></span>
                <span>Stock:</span>
                <span
                    class="stock font-bold {{ $item->availableStock > 0 ? 'text-slate-800' : 'text-red-600' }}">{{ $item->availableStock }}</span>
                <span class="text-slate-400 font-normal">{{ $item->unit?->short_name ?? 'pcs' }}</span>
            </div>

            <span
                class="inline-flex items-center justify-center h-6 w-6 rounded-lg bg-slate-100 text-slate-600 text-xs font-bold group-hover:bg-orange-600 group-hover:text-white transition-all shadow-sm">
                <i class="ri-add-line text-sm"></i>
            </span>
        </div>
    </div>
</div>
