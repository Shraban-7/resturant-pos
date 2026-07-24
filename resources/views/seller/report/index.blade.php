@extends('layouts.admin')
@section('title', 'Report')
@section('page_title', 'Profit / Loss Report')
@section('breadcrumb')
<a href="{{ route('seller.dashboard') }}">Home</a>
<span class="separator">/</span>
<span class="current">Report</span>
@endsection

@section('content')

<div class="page-header">
    <div>
        <p class="page-subtitle">Seller-wide totals plus per-branch comparison</p>
    </div>
    <form class="page-actions flex flex-wrap gap-2 items-end">
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

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="card">
        <div class="px-4 py-3 border-b border-slate-100 font-semibold text-slate-800">Summary</div>
        <div class="table-wrap rounded-b-xl border-0">
            <table class="table">
                <tbody>
                    <tr>
                        <td class="text-slate-600">Total Product Purchase</td>
                        <td class="font-semibold text-slate-900 text-right">{{ money($totalPurchase) }}</td>
                    </tr>
                    <tr>
                        <td class="text-slate-600">Total Sales</td>
                        <td class="font-semibold text-slate-900 text-right">{{ money($totalSales) }}</td>
                    </tr>
                    <tr>
                        <td class="text-slate-600">Cash in Hand</td>
                        <td class="font-semibold text-slate-900 text-right">{{ money($cashInHand) }}</td>
                    </tr>
                    <tr>
                        <td class="text-slate-600">Due</td>
                        <td class="font-semibold text-slate-900 text-right">{{ money($due) }}</td>
                    </tr>
                    @if ($profit > 0)
                        <tr class="bg-emerald-50">
                            <td class="font-bold text-emerald-800">Profit</td>
                            <td class="font-bold text-emerald-700 text-right">{{ money($profit) }}</td>
                        </tr>
                    @else
                        <tr class="bg-red-50">
                            <td class="font-bold text-red-800">Loss</td>
                            <td class="font-bold text-red-700 text-right">{{ money($profit) }}</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="px-4 py-3 border-b border-slate-100 font-semibold text-slate-800">Branch Comparison</div>
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
                            <td class="font-medium text-slate-800">
                                {{ $row['name'] }}
                                @if ($row['code'])
                                    <span class="text-xs text-slate-400">({{ $row['code'] }})</span>
                                @endif
                            </td>
                            <td class="text-right">{{ $row['orders'] }}</td>
                            <td class="text-right">{{ money($row['sales']) }}</td>
                            <td class="text-right {{ $row['profit'] >= 0 ? 'text-emerald-700' : 'text-red-700' }}">
                                {{ money($row['profit']) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-slate-500 py-6">
                                Create branches to see location comparison. Sales without a branch appear as Unassigned / HQ.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
