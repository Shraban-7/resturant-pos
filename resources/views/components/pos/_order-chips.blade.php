@props([
    'title',
    'icon',
    'sales',
    'routeName',
    'showTable' => false,
])
<div class="mb-5">
    <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2 flex items-center gap-1.5">
        <i class="{{ $icon }} text-sm"></i> {{ $title }}
    </h3>
    @if(count($sales ?? []) > 0)
        <div class="flex flex-wrap gap-2">
            @foreach ($sales as $sale)
                @php
                    $params = $showTable ? ['sale' => $sale->order_id] : [""];
                @endphp
                <a href="{{ route($routeName, $params) }}"
                   {{ $showTable ? '' : 'target="_blank"' }}
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-white border border-slate-200 text-xs hover:border-brand-300 hover:bg-brand-50 transition"
                   title="Print Invoice">
                    <span class="font-mono text-slate-500">#{{ $sale->order_id }}</span>
                    <span class="font-semibold text-slate-800">{{ money($sale->payable) }}</span>
                    @if($showTable && $sale?->table?->name)
                        <span class="text-slate-400">·</span>
                        <span class="text-slate-500">{{ $sale->table->name }}</span>
                    @endif
                </a>
            @endforeach
        </div>
    @else
        <p class="text-sm text-slate-400">No {{ strtolower($title) }} yet.</p>
    @endif
</div>
