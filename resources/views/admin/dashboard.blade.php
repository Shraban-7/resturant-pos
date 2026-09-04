@extends('layouts.admin')
@section('title', 'Dashboard')
@section('page_title', 'Dashboard')

@push('header')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
@endpush

@section('content')

<div class="page-header">
    <div>
        <p class="page-subtitle">Overview of your restaurant's performance</p>
    </div>
    <form class="page-actions">
        <div class="input-group input-group-sm">
            <input type="date" class="form-control" name="fromDate" value="{{ request()->fromDate ?? date('Y-m-d') }}">
            <input type="date" class="form-control" name="toDate" value="{{ request()->toDate ?? date('Y-m-d') }}">
            <button type="submit" class="btn btn-primary btn-sm"><i class="ri-filter-3-line"></i> Filter</button>
        </div>
    </form>
</div>

@php
$statCards = [
    ['title' => "Sales ({$totalOrders})", 'value' => money($totalSales), 'route' => route('admin.sales.index'), 'icon' => 'ri-receipt-2-line'],
    ['title' => 'Cash In Hand', 'value' => money($cashInHand), 'route' => route('admin.sales.index'), 'icon' => 'ri-wallet-3-line'],
    ['title' => 'Revenue', 'value' => money($totalRevenue), 'route' => route('admin.sales.index'), 'icon' => 'ri-money-dollar-circle-line'],
    ['title' => 'Due', 'value' => money($due), 'route' => route('admin.sales.index'), 'icon' => 'ri-error-warning-line'],
    ['title' => 'Products', 'value' => $totalProducts, 'route' => route('admin.products.index'), 'icon' => 'ri-box-3-line'],
    ['title' => 'Customers', 'value' => $totalCustomers, 'route' => route('admin.customers.index'), 'icon' => 'ri-team-line'],
];
@endphp

<div class="grid grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4 mb-6">
    @foreach ($statCards as $card)
        <a href="{{ $card['route'] }}" class="no-underline">
            <div class="stat-card">
                <div class="flex items-center justify-between">
                    <h5 class="mb-0 text-sm font-medium text-slate-500">{{ $card['title'] }}</h5>
                    <div class="bg-brand-50 text-brand-600 rounded-lg p-2">
                        <i class="{{ $card['icon'] }} text-xl"></i>
                    </div>
                </div>
                <h4 class="mb-0 mt-3 font-bold text-slate-900 text-xl tracking-tight">
                    {{ $card['value'] }}
                </h4>
            </div>
        </a>
    @endforeach
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-4">
    <div class="xl:col-span-2">
        <div class="card h-full">
            <div class="card-header">
                <div>
                    <h6 class="card-title">Sales Overview</h6>
                    <p class="card-subtitle">Daily sales for the selected period</p>
                </div>
            </div>
            <div class="card-body">
                <canvas id="dailySalesChart" height="120"></canvas>
            </div>
        </div>
    </div>

    <div>
        <div class="card h-full">
            <div class="card-header">
                <div>
                    <h6 class="card-title">Top Selling Items</h6>
                    <p class="card-subtitle">Best performers this period</p>
                </div>
            </div>
            <div class="card-body">
                @forelse($popularItems as $item)
                    <div class="mb-4 last:mb-0">
                        <div class="flex items-center justify-between mb-1.5">
                            <h6 class="mb-0 text-sm font-medium text-slate-800">{{ $item['name'] }}</h6>
                            <span class="text-slate-500 text-xs">{{ $item['sale_count'] }} orders</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="flex-1 progress">
                                <div class="progress-bar bg-brand-600" role="progressbar"
                                     style="width: {{ $item['percentage'] }}%"></div>
                            </div>
                            <span class="text-brand-600 font-bold text-sm w-10 text-right">{{ $item['percentage'] }}%</span>
                        </div>
                    </div>
                @empty
                    <div class="empty-state">
                        <i class="ri-pie-chart-line"></i>
                        <h3>No data yet</h3>
                        <p>Top selling items will appear here once you have sales.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="card mt-6">
    <div class="card-header">
        <div>
            <h6 class="card-title">Recent Orders</h6>
            <p class="card-subtitle">Latest transactions</p>
        </div>
        <a href="{{ route('admin.sales.index') }}" class="btn btn-sm btn-secondary">
            View All <i class="ri-arrow-right-line"></i>
        </a>
    </div>
    <div class="table-wrap rounded-t-none border-0">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Time</th>
                    <th>Amount</th>
                    <th>Paid</th>
                    <th>Due</th>
                    <th class="text-right">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentOrders as $order)
                    <tr>
                        <td class="font-mono text-xs text-slate-500">#{{ $order->order_id }}</td>
                        <td>{{ $order->created_at->format('d M Y, h:i A') }}</td>
                        <td class="font-medium">{{ money($order->payable) }}</td>
                        <td>{{ money($order->paid) }}</td>
                        <td>
                            @if($order->due > 0)
                                <span class="badge badge-danger">{{ money($order->due) }}</span>
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </td>
                        <td class="text-right">
                            <a href="{{ route('admin.sales.invoice', $order->order_id) }}" class="btn btn-primary btn-sm" title="Invoice" target="_blank">
                                <i class="ri-printer-line"></i> Invoice
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-slate-500 py-8">No recent orders.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@push('footer')
<script>
    const salesLabels = {!! json_encode($dailySales->pluck('date')) !!};
    const saleCounts = {!! json_encode($dailySales->pluck('sale_count')) !!};

    const ctx = document.getElementById('dailySalesChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: salesLabels,
            datasets: [{
                label: 'Sales',
                data: saleCounts,
                borderColor: 'rgb(30, 81, 224)',
                backgroundColor: 'rgba(30, 81, 224, 0.1)',
                fill: true,
                tension: 0.3,
                pointRadius: 4,
                pointHoverRadius: 6,
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.04)' } },
                x: { grid: { display: false } }
            }
        }
    });
</script>
@endpush

@endsection

