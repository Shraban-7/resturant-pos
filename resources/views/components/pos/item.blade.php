@props(['item', 'available' => null, 'unlimited' => null, 'addonsCount' => 0])
@php
    // Pure menu: recipe-less dishes & buffets are always available.
    // Falls back to finished-stock display when props are not provided.
    $isUnlimited = $unlimited ?? false;
    $stock = $available ?? $item->availableStock;
@endphp
<div class="item-card group relative bg-white border border-slate-200/90 rounded-xl overflow-hidden cursor-pointer transition-all duration-200 hover:border-orange-400 hover:shadow-md hover:shadow-orange-500/10 hover:-translate-y-0.5 active:translate-y-0 active:scale-[0.98] flex flex-col h-full w-full select-none"
     id="item-{{ $item->id }}" data-id="{{ $item->id }}" data-code="{{ $item->item_code ?? '' }}"
     data-category="{{ $item->category_id }}" data-price="{{ $item->selling_price }}"
     data-stock="{{ $isUnlimited ? 999999 : $stock }}" @if($isUnlimited) data-unlimited="1" @endif>

    {{-- Product Image & Floating Price --}}
    <div class="h-16 sm:h-18 w-full bg-slate-100 overflow-hidden relative shrink-0">
        <div class="absolute top-1 right-1.5 flex items-center gap-0.5">
            <div class="ticket-qty-tag" style="display:none" data-qty-badge="{{ $item->id }}" title="Quantity in ticket">×<span>0</span></div>
        </div>
        <img src="{{ $item->imageUrl() }}" alt="{{ $item->displayName() }}"
            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" loading="lazy">

        {{-- Stock Out Overlay --}}
        @if (!$isUnlimited && $stock <= 0)
            <div class="absolute inset-0 bg-slate-950/70 backdrop-blur-[2px] flex items-center justify-center p-1.5">
                <span
                    class="inline-flex items-center gap-0.5 bg-red-600/90 text-white text-[9px] font-extrabold px-1.5 py-0.5 rounded-full uppercase tracking-wider shadow">
                    <i class="ri-close-circle-line"></i> Sold Out
                </span>
            </div>
        @endif

        {{-- Price Badge --}}
        <div
            class="absolute bottom-1.5 left-1.5 rounded-md bg-slate-950/80 backdrop-blur-md px-1.5 py-0.5 text-white shadow-sm flex items-baseline gap-0.5">
            <span class="text-[9px] text-amber-300 font-bold">৳</span>
            <span
                class="text-[11px] font-extrabold tracking-tight">{{ number_format($item->selling_price, $item->selling_price == (int) $item->selling_price ? 0 : 2) }}</span>
        </div>

    </div>

    {{-- Card Content --}}
    <div class="p-1.5 flex-1 flex flex-col justify-between gap-1 min-h-[42px]">
        <div>
            <h4 class="text-[11px] font-bold text-slate-800 name line-clamp-1 leading-snug group-hover:text-orange-600 transition-colors"
                title="{{ $item->displayName() }}">
                {{ $item->displayName() }}
            </h4>
            @if (!empty($item->name_bn) && app()->getLocale() !== 'bn')
                <p class="text-[9px] text-slate-400 truncate leading-none mt-0.5">{{ $item->name_bn }}</p>
            @endif
        </div>

        <div class="flex items-center justify-between pt-0.5 border-t border-slate-100/80">
            <div class="flex items-center gap-1 text-[9px] font-medium text-slate-500">
                <span
                    class="flex h-1.5 w-1.5 rounded-full {{ $isUnlimited || $stock > 0 ? 'bg-emerald-500' : 'bg-red-500' }}"></span>
                @if ($isUnlimited)
                    <span class="stock font-bold text-emerald-600">Available</span>
                @else
                    <span>Stock:</span>
                    <span
                        class="stock font-bold {{ $stock > 0 ? 'text-slate-800' : 'text-red-600' }}">{{ $stock }}</span>
                    <span class="text-slate-400 font-normal">s</span>
                @endif
            </div>

            <span
                class="inline-flex items-center justify-center h-5 w-5 rounded-md bg-slate-100 text-slate-600 text-[11px] font-bold group-hover:bg-orange-600 group-hover:text-white transition-all shadow-sm">
                <i class="ri-add-line text-xs"></i>
            </span>
        </div>
    </div>
</div>
