@extends('layouts.admin')
@section('title', 'Dashboard')
@section('page_title', 'Dashboard')

@push('header')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
@endpush

@push('styles')
    <style>
        @media (prefers-reduced-motion: no-preference) {
            .dash-rise {
                animation: dash-rise .4s cubic-bezier(0.16, 1, 0.3, 1) both;
            }

            .dash-rise:nth-child(1) {
                animation-delay: .03s;
            }

            .dash-rise:nth-child(2) {
                animation-delay: .06s;
            }

            .dash-rise:nth-child(3) {
                animation-delay: .09s;
            }

            .dash-rise:nth-child(4) {
                animation-delay: .12s;
            }

            .dash-rise:nth-child(5) {
                animation-delay: .15s;
            }

            .dash-rise:nth-child(6) {
                animation-delay: .18s;
            }

            @keyframes dash-rise {
                from {
                    opacity: 0;
                    transform: translateY(8px);
                }

                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
        }
    </style>
@endpush

@section('content')

    {{-- Top Header / Date Filter bar --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6 pb-2 border-b border-slate-200/80">
        <div>
            <h1 class="text-xl sm:text-2xl font-black tracking-tight text-slate-900">Executive Overview</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Real-time performance metrics, sales velocity, and inventory
                pulse</p>
        </div>
        <form class="flex items-center gap-2" method="GET" action="{{ route('admin.dashboard') }}">
            <div
                class="inline-flex items-center rounded-xl bg-white p-1 shadow-sm border border-slate-200/90 hover:border-slate-300 transition-colors">
                <div class="flex items-center px-2.5 text-slate-400">
                    <i class="ri-calendar-event-line text-sm"></i>
                </div>
                <input type="date" name="fromDate" value="{{ request()->fromDate ?? date('Y-m-d') }}"
                    class="border-0 bg-transparent py-1.5 px-2 text-xs font-medium text-slate-700 focus:outline-none focus:ring-0">
                <span class="text-slate-300 font-light px-1">to</span>
                <input type="date" name="toDate" value="{{ request()->toDate ?? date('Y-m-d') }}"
                    class="border-0 bg-transparent py-1.5 px-2 text-xs font-medium text-slate-700 focus:outline-none focus:ring-0">
                <button type="submit"
                    class="ml-1 inline-flex items-center gap-1.5 rounded-lg bg-orange-600 hover:bg-orange-700 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition active:scale-95">
                    <i class="ri-filter-3-line"></i> Filter
                </button>
                @if (request()->has('fromDate') || request()->has('toDate'))
                    <a href="{{ route('admin.dashboard') }}" class="px-2 text-xs text-slate-400 hover:text-slate-600"
                        title="Reset filter">
                        <i class="ri-close-circle-line text-base"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Modern Shift & Sales Velocity Summary (Crisp White / Elegant Luminous Card) --}}
    <div
        class="dash-rise mb-6 overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm hover:shadow transition-shadow">
        <div class="h-1.5 bg-orange-500"></div>
        <div class="p-6 sm:p-7">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                <div>
                    <div
                        class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-orange-50 border border-orange-100/80 text-[11px] font-bold uppercase tracking-wider text-orange-700">
                        <span class="relative flex h-2 w-2">
                            <span
                                class="animate-ping absolute inline-flex h-full w-full rounded-full bg-orange-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-orange-500"></span>
                        </span>
                        Shift Summary
                        <span class="text-orange-300">|</span>
                        <span class="font-semibold text-slate-600 normal-case tracking-normal">
                            {{ request()->fromDate ? \Carbon\Carbon::parse(request()->fromDate)->format('d M Y') . ' – ' . \Carbon\Carbon::parse(request()->toDate)->format('d M Y') : 'Today, ' . date('d M Y') }}
                        </span>
                    </div>
                    <div class="mt-4">
                        <span class="text-xs font-medium uppercase tracking-wider text-slate-400">Total Net Sales</span>
                        <div
                            class="font-mono text-3xl sm:text-5xl font-black tracking-tight text-slate-900 tabular-nums mt-1">
                            {{ money($totalSales) }}
                        </div>
                    </div>
                    <div class="mt-3 flex items-center gap-3 text-xs text-slate-500">
                        <span
                            class="inline-flex items-center gap-1.5 font-medium text-slate-700 bg-slate-100/80 px-2.5 py-1 rounded-md">
                            <i class="ri-shopping-bag-3-line text-orange-600"></i>
                            <strong class="text-slate-900">{{ $totalOrders }}</strong>
                            {{ $totalOrders === 1 ? 'order processed' : 'orders processed' }}
                        </span>
                        <span>Average per order: <strong
                                class="font-mono font-bold text-slate-800">{{ money($totalOrders > 0 ? $totalSales / $totalOrders : 0) }}</strong></span>
                    </div>
                </div>

                {{-- 3 Key Financial Highlights --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 lg:min-w-[440px]">
                    <div
                        class="group rounded-xl border border-slate-100 bg-slate-50/70 p-4 transition hover:bg-white hover:border-slate-200 hover:shadow-sm">
                        <div class="flex items-center justify-between text-slate-400 mb-2">
                            <span class="text-[11px] font-bold uppercase tracking-wider">Orders</span>
                            <i
                                class="ri-shopping-cart-2-line text-base text-slate-400 group-hover:text-orange-600 transition-colors"></i>
                        </div>
                        <p class="font-mono text-2xl font-black text-slate-900 tabular-nums">{{ $totalOrders }}</p>
                        <p class="text-[11px] text-slate-500 mt-1">Completed tickets</p>
                    </div>

                    <div
                        class="group rounded-xl border border-emerald-100/70 bg-emerald-50/40 p-4 transition hover:bg-white hover:border-emerald-200 hover:shadow-sm">
                        <div class="flex items-center justify-between text-emerald-600 mb-2">
                            <span class="text-[11px] font-bold uppercase tracking-wider">Cash Collected</span>
                            <i
                                class="ri-wallet-3-line text-base text-emerald-500 group-hover:scale-110 transition-transform"></i>
                        </div>
                        <p class="font-mono text-2xl font-black text-emerald-700 tabular-nums">{{ money($cashInHand) }}</p>
                        <p class="text-[11px] text-emerald-600/80 mt-1">Liquid / paid in hand</p>
                    </div>

                    <div
                        class="group rounded-xl border border-rose-100/70 bg-rose-50/40 p-4 transition hover:bg-white hover:border-rose-200 hover:shadow-sm">
                        <div class="flex items-center justify-between text-rose-500 mb-2">
                            <span class="text-[11px] font-bold uppercase tracking-wider">Due / Pending</span>
                            <i
                                class="ri-error-warning-line text-base text-rose-500 group-hover:scale-110 transition-transform"></i>
                        </div>
                        <p class="font-mono text-2xl font-black text-rose-600 tabular-nums">{{ money($due) }}</p>
                        <p class="text-[11px] text-rose-600/80 mt-1">Receivables remaining</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Bottom metrics breakdown strip --}}
        <div
            class="border-t border-slate-100 bg-slate-50/60 px-6 sm:px-7 py-3 flex flex-wrap items-center justify-between gap-y-2 text-xs text-slate-600">
            <div class="flex flex-wrap items-center gap-x-5 gap-y-2">
                <span class="inline-flex items-center gap-1.5">
                    <span class="h-2 w-2 rounded-full bg-orange-500"></span>
                    <span>Gross Revenue:</span>
                    <strong class="font-mono font-bold text-slate-800">{{ money($totalRevenue) }}</strong>
                </span>
                <span class="text-slate-300">/</span>
                <span class="inline-flex items-center gap-1.5">
                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                    <span>Collected Ratio:</span>
                    <strong class="font-mono font-bold text-emerald-700">
                        {{ $totalSales > 0 ? round(($cashInHand / $totalSales) * 100, 1) : 0 }}%
                    </strong>
                </span>
                <span class="text-slate-300">/</span>
                <span class="inline-flex items-center gap-1.5">
                    <span class="h-2 w-2 rounded-full bg-rose-400"></span>
                    <span>Outstanding Ratio:</span>
                    <strong class="font-mono font-bold text-rose-700">
                        {{ $totalSales > 0 ? round(($due / $totalSales) * 100, 1) : 0 }}%
                    </strong>
                </span>
            </div>
            <div class="text-[11px] text-slate-400">
                Auto-refreshes on filter change
            </div>
        </div>
    </div>

    @php
        $statCards = [
            [
                'title' => "Sales ({$totalOrders})",
                'value' => money($totalSales),
                'subtitle' => 'Total invoiced',
                'route' => route('admin.sales.index'),
                'icon' => 'ri-receipt-2-line',
                'iconBg' => 'bg-orange-50 text-orange-600 border border-orange-100',
                'accent' => 'hover:border-orange-300',
                'badge' => 'Orders',
            ],
            [
                'title' => 'Cash in Hand',
                'value' => money($cashInHand),
                'subtitle' => 'Payment received',
                'route' => route('admin.sales.index'),
                'icon' => 'ri-wallet-3-line',
                'iconBg' => 'bg-emerald-50 text-emerald-600 border border-emerald-100',
                'accent' => 'hover:border-emerald-300',
                'badge' => 'Paid',
            ],
            [
                'title' => 'Gross Revenue',
                'value' => money($totalRevenue),
                'subtitle' => 'Sales & net gains',
                'route' => route('admin.sales.index'),
                'icon' => 'ri-funds-box-line',
                'iconBg' => 'bg-sky-50 text-sky-600 border border-sky-100',
                'accent' => 'hover:border-sky-300',
                'badge' => 'Revenue',
            ],
            [
                'title' => 'Customer Due',
                'value' => money($due),
                'subtitle' => 'Unsettled balances',
                'route' => route('admin.sales.index'),
                'icon' => 'ri-error-warning-line',
                'iconBg' => 'bg-rose-50 text-rose-600 border border-rose-100',
                'accent' => 'hover:border-rose-300',
                'badge' => 'Due',
            ],
            [
                'title' => 'Active Products',
                'value' => $totalProducts,
                'subtitle' => 'Live in catalog',
                'route' => route('admin.products.index'),
                'icon' => 'ri-restaurant-2-line',
                'iconBg' => 'bg-amber-50 text-amber-600 border border-amber-100',
                'accent' => 'hover:border-amber-300',
                'badge' => 'Catalog',
            ],
            [
                'title' => 'Total Customers',
                'value' => $totalCustomers,
                'subtitle' => 'Registered diners',
                'route' => route('admin.customers.index'),
                'icon' => 'ri-user-heart-line',
                'iconBg' => 'bg-violet-50 text-violet-600 border border-violet-100',
                'accent' => 'hover:border-violet-300',
                'badge' => 'Patrons',
            ],
        ];
    @endphp

    {{-- KPI Stats Grid --}}
    <div class="grid grid-cols-2 lg:grid-cols-3 gap-3.5 sm:gap-4 mb-6">
        @foreach ($statCards as $i => $card)
            <a href="{{ $card['route'] }}" class="no-underline dash-rise group">
                <div
                    class="relative overflow-hidden rounded-2xl border border-slate-200/90 bg-white p-4 sm:p-5 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md {{ $card['accent'] }}">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <span
                                    class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ $card['title'] }}</span>
                            </div>
                            <div
                                class="mt-2.5 truncate font-mono text-xl sm:text-2xl font-black tracking-tight text-slate-900 tabular-nums">
                                {{ $card['value'] }}
                            </div>
                            <p class="mt-1 text-xs text-slate-400 font-medium truncate">{{ $card['subtitle'] }}</p>
                        </div>
                        <div
                            class="shrink-0 rounded-xl p-2.5 sm:p-3 {{ $card['iconBg'] }} transition-transform duration-200 group-hover:scale-110">
                            <i class="{{ $card['icon'] }} text-xl"></i>
                        </div>
                    </div>
                    <div
                        class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                        <span class="font-medium text-slate-400">View details</span>
                        <i
                            class="ri-arrow-right-s-line text-slate-400 group-hover:text-slate-700 group-hover:translate-x-0.5 transition-all"></i>
                    </div>
                </div>
            </a>
        @endforeach
    </div>

    {{-- Analytics & Best Sellers Grid --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-5">
        {{-- Sales Overview Chart Card --}}
        <div class="xl:col-span-2">
            <div class="rounded-2xl border border-slate-200/90 bg-white shadow-sm overflow-hidden flex flex-col h-full">
                <div class="px-5 sm:px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Sales Velocity</h2>
                        <p class="text-xs text-slate-400 mt-0.5">Daily completed orders during the selected period</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span
                            class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 font-mono">
                            {{ $dailySales->count() }} {{ $dailySales->count() === 1 ? 'day recorded' : 'days recorded' }}
                        </span>
                    </div>
                </div>
                <div class="p-5 sm:p-6 flex-1 flex flex-col justify-center">
                    @if ($dailySales->isNotEmpty())
                        <div class="relative w-full h-[260px]">
                            <canvas id="dailySalesChart"></canvas>
                        </div>
                    @else
                        <div class="py-16 text-center">
                            <div
                                class="mx-auto w-12 h-12 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-400 mb-3">
                                <i class="ri-line-chart-line text-2xl"></i>
                            </div>
                            <h4 class="text-sm font-bold text-slate-700">No sales records found</h4>
                            <p class="text-xs text-slate-400 mt-1 max-w-xs mx-auto">Sales transaction velocity will chart
                                here automatically once orders are completed.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Top Performing Dishes / Items --}}
        <div>
            <div class="rounded-2xl border border-slate-200/90 bg-white shadow-sm overflow-hidden flex flex-col h-full">
                <div class="px-5 sm:px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Top Selling Items</h2>
                        <p class="text-xs text-slate-400 mt-0.5">Most ordered dishes by popularity</p>
                    </div>
                    <span class="text-xs font-medium text-slate-400">Share %</span>
                </div>
                <div class="p-5 sm:p-6 flex-1">
                    @forelse($popularItems as $item)
                        <div class="mb-4 last:mb-0 group">
                            <div class="flex items-center gap-3">
                                <div
                                    class="shrink-0 w-7 h-7 rounded-lg flex items-center justify-center font-mono text-xs font-extrabold {{ $loop->iteration === 1 ? 'bg-amber-100 text-amber-700 border border-amber-200' : ($loop->iteration === 2 ? 'bg-slate-100 text-slate-700' : ($loop->iteration === 3 ? 'bg-orange-50 text-orange-700' : 'text-slate-400 bg-slate-50')) }}">
                                    {{ $loop->iteration }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between gap-2 mb-1.5">
                                        <h3
                                            class="truncate text-xs sm:text-sm font-semibold text-slate-800 group-hover:text-orange-600 transition-colors">
                                            {{ $item['name'] }}
                                        </h3>
                                        <span class="shrink-0 text-slate-400 text-xs font-mono">
                                            {{ $item['sale_count'] }} <span
                                                class="hidden sm:inline">{{ $item['sale_count'] === 1 ? 'order' : 'orders' }}</span>
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-2.5">
                                        <div class="flex-1 h-2 rounded-full bg-slate-100 overflow-hidden">
                                            <div class="h-full rounded-full bg-orange-500 transition-all duration-500"
                                                style="width: {{ max(4, $item['percentage']) }}%"></div>
                                        </div>
                                        <span
                                            class="font-mono font-bold text-slate-600 text-xs w-9 text-right tabular-nums">{{ $item['percentage'] }}%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="py-12 text-center">
                            <div
                                class="mx-auto w-10 h-10 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-400 mb-2">
                                <i class="ri-pie-chart-line text-xl"></i>
                            </div>
                            <h4 class="text-xs font-bold text-slate-700">No popular items</h4>
                            <p class="text-[11px] text-slate-400 mt-0.5">Item rankings appear as guests place orders.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    @php
        $orderTypeBadges = [
            'dine_in' => 'bg-orange-50 text-orange-700 border border-orange-100',
            'takeaway' => 'bg-sky-50 text-sky-700 border border-sky-100',
            'delivery' => 'bg-emerald-50 text-emerald-700 border border-emerald-100',
            'retail' => 'bg-slate-100 text-slate-700 border border-slate-200',
        ];
        $paymentBadges = [
            'cash' => 'bg-emerald-50 text-emerald-700 border border-emerald-100',
            'card' => 'bg-sky-50 text-sky-700 border border-sky-100',
            'mobile_banking' => 'bg-violet-50 text-violet-700 border border-violet-100',
            'gift_card' => 'bg-amber-50 text-amber-700 border border-amber-100',
        ];
    @endphp

    {{-- Recent Transactions Table --}}
    <div class="mt-6 rounded-2xl border border-slate-200/90 bg-white shadow-sm overflow-hidden">
        <div class="px-5 sm:px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-slate-900">Recent Transactions</h2>
                <p class="text-xs text-slate-400 mt-0.5">Latest finalized dining and takeaway tickets</p>
            </div>
            <a href="{{ route('admin.sales.index') }}"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-colors">
                View All Sales <i class="ri-arrow-right-line text-xs"></i>
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr
                        class="border-b border-slate-100 bg-slate-50/60 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        <th class="py-3 px-5">Order ID</th>
                        <th class="py-3 px-4">Date & Time</th>
                        <th class="py-3 px-4">Type</th>
                        <th class="py-3 px-4">Payment</th>
                        <th class="py-3 px-4 text-right">Payable</th>
                        <th class="py-3 px-4 text-right">Paid</th>
                        <th class="py-3 px-4 text-right">Due</th>
                        <th class="py-3 px-5 text-right">Invoice</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                    @forelse($recentOrders as $order)
                        @php
                            $type = $order->order_type;
                            $typeValue = $type instanceof \App\Enums\OrderType ? $type->value : ($type ?: 'dine_in');
                            $typeBadge = $orderTypeBadges[$typeValue] ?? 'bg-slate-100 text-slate-700';
                            $payValue = $order->payment_option ?: 'cash';
                            $payBadge = $paymentBadges[$payValue] ?? 'bg-slate-50 text-slate-600';
                            $payLabel = ($method = \App\Enums\PaymentMethod::tryFrom($payValue))
                                ? $method->label()
                                : 'Cash';
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3.5 px-5 font-mono font-semibold text-slate-900">
                                #{{ $order->order_id }}
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap text-slate-500">
                                {{ $order->created_at->format('d M Y, h:i A') }}
                            </td>
                            <td class="py-3.5 px-4">
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold {{ $typeBadge }}">
                                    {{ \App\Enums\OrderType::tryFrom($typeValue)?->label() ?? ucfirst($typeValue) }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold {{ $payBadge }}">
                                    {{ $payLabel }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono font-bold text-slate-900 tabular-nums">
                                {{ money($order->payable) }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono font-bold text-emerald-600 tabular-nums">
                                {{ money($order->paid) }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono tabular-nums">
                                @if ($order->due > 0)
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-100">
                                        {{ money($order->due) }}
                                    </span>
                                @else
                                    <span class="text-slate-300 font-light">—</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-5 text-right">
                                <a href="{{ route('admin.sales.invoice', $order->order_id) }}"
                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-semibold bg-orange-50 text-orange-700 hover:bg-orange-100 border border-orange-200/80 transition"
                                    target="_blank" title="Print Invoice">
                                    <i class="ri-printer-line text-sm"></i> Print
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-12 text-slate-400">
                                <i class="ri-inbox-line text-2xl block mb-1 text-slate-300"></i>
                                No orders found for this period.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @push('footer')
        <script>
            const chartEl = document.getElementById('dailySalesChart');
            if (chartEl) {
                const salesLabels = {!! json_encode($dailySales->pluck('date')) !!};
                const saleCounts = {!! json_encode($dailySales->pluck('sale_count')) !!};
                const ctx = chartEl.getContext('2d');

                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: salesLabels,
                        datasets: [{
                            label: 'Orders',
                            data: saleCounts,
                            borderColor: '#ea580c',
                            backgroundColor: 'rgba(234, 88, 12, 0.10)',
                            fill: true,
                            tension: 0.38,
                            pointRadius: 3.5,
                            pointBackgroundColor: '#ffffff',
                            pointBorderColor: '#ea580c',
                            pointBorderWidth: 2,
                            pointHoverRadius: 6,
                            pointHoverBackgroundColor: '#ea580c',
                            pointHoverBorderColor: '#ffffff',
                            pointHoverBorderWidth: 2,
                            borderWidth: 2.5
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            mode: 'index',
                            intersect: false
                        },
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                backgroundColor: '#1e293b',
                                titleColor: '#cbd5e1',
                                bodyColor: '#ffffff',
                                cornerRadius: 8,
                                padding: 10,
                                titleFont: {
                                    family: 'Inter, sans-serif',
                                    size: 11
                                },
                                bodyFont: {
                                    family: 'ui-monospace, monospace',
                                    size: 13,
                                    weight: 'bold'
                                },
                                displayColors: false,
                                callbacks: {
                                    label: function(context) {
                                        return ' ' + context.parsed.y + (context.parsed.y === 1 ? ' order' :
                                            ' orders');
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    color: '#94a3b8',
                                    font: {
                                        family: 'Inter, sans-serif',
                                        size: 11
                                    },
                                    stepSize: 1
                                },
                                grid: {
                                    color: 'rgba(15, 23, 42, 0.05)',
                                    borderDash: [4, 4]
                                }
                            },
                            x: {
                                ticks: {
                                    color: '#94a3b8',
                                    font: {
                                        family: 'ui-monospace, monospace',
                                        size: 11
                                    },
                                    maxRotation: 0,
                                    autoSkip: true,
                                    maxTicksLimit: 10
                                },
                                grid: {
                                    display: false
                                }
                            }
                        }
                    }
                });
            }
        </script>
    @endpush

@endsection
