<div x-data="{ open: false }" @keydown.escape.window="open = false">
    <template x-teleport="body">
        <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none">
            <div class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm" @click="open = false"></div>
            <div class="relative w-full max-w-md bg-white rounded-3xl shadow-2xl border border-slate-200/90 max-h-[85vh] flex flex-col overflow-hidden"
                @click.stop>
                <div
                    class="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-slate-50/70 shrink-0">
                    <div class="flex items-center gap-2.5">
                        <span
                            class="flex items-center justify-center h-8 w-8 rounded-xl bg-orange-600 text-white shadow-sm">
                            <i class="ri-receipt-2-line text-base"></i>
                        </span>
                        <div>
                            <h3 class="text-base font-bold text-slate-900 leading-tight">Recent Sales</h3>
                            <p class="text-[11px] text-slate-500">Quick invoice access</p>
                        </div>
                    </div>
                    <button type="button"
                        class="h-8 w-8 rounded-full hover:bg-slate-200 text-slate-400 hover:text-slate-700 flex items-center justify-center transition"
                        @click="open = false" aria-label="Close">
                        <i class="ri-close-line text-lg"></i>
                    </button>
                </div>
                <div class="flex-1 overflow-y-auto p-4">
                    @if (count($sales) === 0)
                        <div class="empty-state py-12 text-center">
                            <div
                                class="h-12 w-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center text-xl mx-auto mb-2">
                                <i class="ri-inbox-line"></i>
                            </div>
                            <h3 class="text-sm font-bold text-slate-700">No Recent Sales</h3>
                            <p class="text-xs text-slate-400 mt-1">Completed orders will show here for instant
                                re-printing.</p>
                        </div>
                    @else
                        <ul class="space-y-2">
                            @foreach ($sales as $sale)
                                <li
                                    class="p-3 bg-slate-50 hover:bg-orange-50/50 border border-slate-200/80 hover:border-orange-200 rounded-2xl flex items-center justify-between transition group">
                                    <div>
                                        <div class="flex items-center gap-1.5">
                                            <span
                                                class="font-mono font-bold text-slate-800 text-xs group-hover:text-orange-600 transition-colors">#{{ $sale->order_id }}</span>
                                            <span class="text-[10px] text-slate-400">·
                                                {{ $sale->created_at->diffForHumans() }}</span>
                                        </div>
                                        <div class="text-xs text-slate-500 mt-1">
                                            Payable: <span
                                                class="font-extrabold text-slate-900">{{ money($sale->payable ?? $sale->subtotal) }}</span>
                                        </div>
                                    </div>
                                    <a class="inline-flex items-center gap-1 bg-white hover:bg-slate-900 text-slate-700 hover:text-white border border-slate-200 text-xs font-bold px-3 py-1.5 rounded-xl shadow-sm transition"
                                        href="{{ route('admin.sales.invoice', $sale->order_id) }}" target="_blank"
                                        title="Print Invoice">
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
