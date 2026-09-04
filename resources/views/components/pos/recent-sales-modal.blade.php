<div x-data="{ open: false }" @keydown.escape.window="open = false">
    <template x-teleport="body">
        <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none">
            <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" @click="open = false"></div>
            <div class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl border border-slate-200 max-h-[80vh] flex flex-col" @click.stop>
                <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
                    <h3 class="text-base font-semibold text-slate-900 flex items-center gap-2">
                        <i class="ri-receipt-2-line text-brand-600"></i>
                        Recent Sales
                    </h3>
                    <button type="button" class="btn btn-ghost btn-icon" @click="open = false" aria-label="Close">
                        <i class="ri-close-line text-xl"></i>
                    </button>
                </div>
                <div class="flex-1 overflow-y-auto p-2">
                    @if(count($sales) === 0)
                        <div class="empty-state py-10">
                            <i class="ri-inbox-line"></i>
                            <h3>No recent sales</h3>
                            <p>Your sales will appear here once you make some.</p>
                        </div>
                    @else
                        <ul class="divide-y divide-slate-100">
                            @foreach ($sales as $sale)
                                <li class="px-3 py-3 flex items-center justify-between hover:bg-slate-50 rounded-lg">
                                    <div>
                                        <div class="text-xs text-slate-500">{{ $sale->created_at->diffForHumans() }}</div>
                                        <div class="font-medium text-slate-900">#{{ $sale->order_id }}</div>
                                        <div class="text-sm text-slate-600">Total: <span class="font-semibold text-slate-900">{{ number_format($sale->subtotal, 2) }}</span></div>
                                    </div>
                                    <a class="btn btn-success btn-sm" href="" target="_blank" title="Print Invoice">
                                        <i class="ri-printer-line"></i> Print
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </template>
</div>
