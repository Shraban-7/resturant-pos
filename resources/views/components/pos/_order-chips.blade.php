@props([
    'title',
    'icon',
    'sales',
    'routeName',
    'showTable' => false,
])
<div class="mb-4">
    <div class="flex items-center gap-2 mb-2">
        <span class="flex items-center justify-center h-5 w-5 rounded-md bg-slate-200/80 text-slate-600 text-xs">
            <i class="{{ $icon }}"></i>
        </span>
        <h3 class="text-xs font-bold text-slate-600 uppercase tracking-wider">
            {{ $title }}
        </h3>
        <span class="text-[10px] font-extrabold px-1.5 py-0.2 rounded-full {{ count($sales ?? []) > 0 ? 'bg-orange-100 text-orange-700' : 'bg-slate-200 text-slate-500' }}">
            {{ count($sales ?? []) }}
        </span>
    </div>

    @if(count($sales ?? []) > 0)
        <div class="flex items-center gap-2 overflow-x-auto no-scrollbar py-0.5 -mx-1 px-1">
            @foreach ($sales as $sale)
                @php
                    $params = $showTable ? ['sale' => $sale->order_id] : [""];
                    $chipTableName = ($sale->getRelationValue('diningTable') ?? $sale->getRelationValue('table'))?->name ?? $sale->diningTable?->name ?? $sale->table?->name ?? null;
                @endphp
                <a href="{{ route($routeName, $params) }}"
                   {{ $showTable ? '' : 'target="_blank"' }}
                   class="shrink-0 inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-white border border-slate-200/90 hover:border-orange-400 hover:bg-orange-50/50 shadow-sm transition-all group"
                   title="{{ $showTable ? 'Resume Order' : 'Print Invoice' }}">
                    <span class="text-[11px] font-bold font-mono text-slate-500 group-hover:text-orange-600 transition-colors">#{{ $sale->order_id }}</span>
                    <span class="text-xs font-extrabold text-slate-800">{{ money($sale->payable) }}</span>
                    @if($showTable && $chipTableName)
                        <span class="text-[10px] font-semibold text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded-md border border-emerald-200/60 flex items-center gap-1">
                            <i class="ri-restaurant-line text-[10px]"></i>{{ $chipTableName }}
                        </span>
                    @endif
                </a>
            @endforeach
        </div>
    @else
        <p class="text-xs text-slate-400 italic">No {{ strtolower($title) }} at the moment.</p>
    @endif
</div>
