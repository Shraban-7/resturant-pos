@extends('layouts.admin')
@section('title', 'Report')
@section('page_title', 'Profit / Loss Report')
@section('breadcrumb')
<a href="{{ route('supplier.dashboard') }}">Home</a>
<span class="separator">/</span>
<span class="current">Report</span>
@endsection

@section('content')

<div class="page-header">
    <div></div>
    <form class="page-actions">
        <div class="input-group input-group-sm">
            <input type="date" class="form-control" name="fromDate" value="{{ request()->fromDate ?? date('Y-m-d') }}">
            <input type="date" class="form-control" name="toDate" value="{{ request()->toDate ?? date('Y-m-d') }}">
            <button type="submit" class="btn btn-primary btn-sm"><i class="ri-filter-3-line"></i> Filter</button>
        </div>
    </form>
</div>

<div class="max-w-2xl">
    <div class="card">
        <div class="table-wrap rounded-xl border-0">
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
</div>

@endsection
