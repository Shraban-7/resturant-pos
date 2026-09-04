@extends('layouts.admin')
@section('title', 'Report')
@section('page_title', 'Profit / Loss Report')
@section('breadcrumb')
<a href="{{ route('admin.dashboard') }}">Home</a>
<span class="separator">/</span>
<span class="current">Report</span>
@endsection

@section('content')

<div class="page-header">
    <div>
        <p class="page-subtitle">Seller-wide totals plus per-branch comparison · {{ $fromDate }} to {{ $toDate }}</p>
    </div>
    <form class="page-actions flex flex-wrap gap-2 items-end" method="GET">
        <div>
            <label class="form-label text-xs">From</label>
            <input type="date" class="form-control form-control-sm" name="fromDate" value="{{ $fromDate }}">
        </div>
        <div>
            <label class="form-label text-xs">To</label>
            <input type="date" class="form-control form-control-sm" name="toDate" value="{{ $toDate }}">
        </div>
        @if ($branches->isNotEmpty())
            <div>
                <label class="form-label text-xs">Branch</label>
                <select name="branch_id" class="form-control form-control-sm">
                    <option value="">All branches</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" @selected((string) $branchFilter === (string) $branch->id)>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                    <option value="unassigned" @selected($branchFilter === 'unassigned')>Unassigned / HQ</option>
                </select>
            </div>
        @endif
        <button type="submit" class="btn btn-primary btn-sm"><i class="ri-filter-3-line"></i> Filter</button>
    </form>
</div>

@php
$maxBranchSales = max([1, ...$branchComparison->pluck('sales')->all()]);
$statCards = [
    ['title' => 'Total Sales', 'value' => money($totalSales), 'icon' => 'ri-receipt-2-line'],
    ['title' => 'Cash in Hand', 'value' => money($cashInHand), 'icon' => 'ri-wallet-3-line'],
    ['title' => 'Due', 'value' => money($due), 'icon' => 'ri-error-warning-line'],
    ['title' => 'Product Purchase', 'value' => money($totalPurchase), 'icon' => 'ri-shopping-basket-2-line'],
];
@endphp

<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-6">
    @foreach ($statCards as $card)
        <div class="stat-card">
            <div class="flex items-center justify-between">
                <h5 class="mb-0 text-sm font-medium text-slate-500">{{ $card['title'] }}</h5>
                <div class="bg-brand-50 text-brand-600 rounded-lg p-2">
                    <i class="{{ $card['icon'] }} text-xl"></i>
                </div>
            </div>
            <h4 class="mb-0 mt-3 font-bold text-slate-900 text-xl tracking-tight">{{ $card['value'] }}</h4>
        </div>
    @endforeach
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-4">
    <div class="card h-full {{ $profit >= 0 ? 'border-emerald-200' : 'border-red-200' }}">
        <div class="card-header">
            <div>
                <h6 class="card-title">Net Result</h6>
                <p class="card-subtitle">Sales minus purchase cost</p>
            </div>
            <span class="badge {{ $profit >= 0 ? 'badge-success' : 'badge-danger' }}">{{ $profit >= 0 ? 'Profit' : 'Loss' }}</span>
        </div>
        <div class="p-5 text-center">
            <p class="text-3xl font-bold {{ $profit >= 0 ? 'text-emerald-700' : 'text-red-700' }}">{{ money($profit) }}</p>
            <p class="text-sm text-slate-500 mt-1">
                Margin {{ $totalSales > 0 ? number_format($profit / $totalSales * 100, 1) : 0 }}% of sales
            </p>
            <div class="h-2 rounded-full bg-slate-100 mt-4 overflow-hidden">
                <div class="h-full rounded-full {{ $profit >= 0 ? 'bg-emerald-500' : 'bg-red-500' }}"
                     style="width: {{ $totalSales > 0 ? min(100, abs($profit / $totalSales * 100)) : 0 }}%"></div>
            </div>
        </div>
    </div>

    <div class="xl:col-span-2">
        <div class="card h-full">
            <div class="card-header">
                <div>
                    <h6 class="card-title">Branch Comparison</h6>
                    <p class="card-subtitle">Orders, sales and profit by location</p>
                </div>
            </div>
            <div class="table-wrap rounded-b-xl border-0">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Branch</th>
                            <th class="text-right">Orders</th>
                            <th class="text-right">Sales</th>
                            <th class="text-right">Profit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($branchComparison as $row)
                            <tr>
                                <td>
                                    <span class="font-medium text-slate-800">{{ $row['name'] }}</span>
                                    @if ($row['code'])
                                        <span class="text-xs text-slate-400">({{ $row['code'] }})</span>
                                    @endif
                                    <div class="h-1.5 rounded-full bg-slate-100 mt-1.5 overflow-hidden max-w-[12rem]">
                                        <div class="h-full rounded-full bg-brand-500" style="width: {{ $row['sales'] / $maxBranchSales * 100 }}%"></div>
                                    </div>
                                </td>
                                <td class="text-right">{{ $row['orders'] }}</td>
                                <td class="text-right">{{ money($row['sales']) }}</td>
                                <td class="text-right font-semibold {{ $row['profit'] >= 0 ? 'text-emerald-700' : 'text-red-700' }}">
                                    {{ money($row['profit']) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">
                                    <div class="empty-state">
                                        <i class="ri-store-2-line"></i>
                                        <h3>No branches yet</h3>
                                        <p>Create branches to see location comparison. Sales without a branch appear as Unassigned / HQ.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="card mt-6">
    <div class="card-header">
        <div>
            <h6 class="card-title">Wholesale / Supplier</h6>
            <p class="card-subtitle">Same calculations as the previous supplier panel</p>
        </div>
        <span class="badge {{ ($supplierNet ?? 0) >= 0 ? 'badge-success' : 'badge-danger' }}">
            Net {{ money($supplierNet ?? 0) }}
        </span>
    </div>
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 p-4">
        @php
        $supplierCards = [
            ['title' => 'Purchase', 'value' => money($supplierPurchase ?? 0), 'icon' => 'ri-shopping-basket-2-line'],
            ['title' => 'Sales', 'value' => money($supplierTotal ?? 0), 'icon' => 'ri-truck-line'],
            ['title' => 'Cash in Hand', 'value' => money($supplierPaid ?? 0), 'icon' => 'ri-wallet-3-line'],
            ['title' => 'Due', 'value' => money($supplierDue ?? 0), 'icon' => 'ri-error-warning-line'],
            ['title' => 'Profit', 'value' => money($supplierProfit ?? 0), 'icon' => 'ri-arrow-up-line', 'class' => 'text-emerald-700'],
            ['title' => 'Loss', 'value' => money($supplierLoss ?? 0), 'icon' => 'ri-arrow-down-line', 'class' => 'text-red-700'],
        ];
        @endphp
        @foreach ($supplierCards as $card)
            <div class="stat-card">
                <div class="flex items-center justify-between">
                    <h5 class="mb-0 text-sm font-medium text-slate-500">{{ $card['title'] }}</h5>
                    <div class="bg-brand-50 text-brand-600 rounded-lg p-2">
                        <i class="{{ $card['icon'] }} text-xl"></i>
                    </div>
                </div>
                <h4 class="mb-0 mt-3 font-bold text-xl tracking-tight {{ $card['class'] ?? 'text-slate-900' }}">{{ $card['value'] }}</h4>
            </div>
        @endforeach
    </div>
</div>

@endsection

