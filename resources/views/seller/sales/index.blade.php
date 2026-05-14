@extends('layouts.admin')
@section('title', 'Sales')
@section('page_title', 'Sales')
@section('breadcrumb')
<a href="{{ route('seller.dashboard') }}">Home</a>
<span class="separator">/</span>
<span class="current">Sales</span>
@endsection

@section('content')

<div class="page-header">
    <div>
        <p class="page-subtitle">Total Sales: <span class="font-semibold text-brand-600">{{ money($totalSales) }}</span></p>
    </div>
</div>

<div class="card">
    <div class="table-wrap rounded-t-xl border-0">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Customer | Phone</th>
                    <th>Total</th>
                    <th>Paid</th>
                    <th>Table (Waiter)</th>
                    <th class="text-right">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($sales as $sale)
                    <tr>
                        <td class="text-slate-600">{{ $sale->created_at->format('d M Y, h:i A') }}</td>
                        <td>
                            @if ($sale->customer)
                                <span class="font-medium text-slate-800">{{ $sale->customer->name }}</span>
                                <span class="text-slate-400 mx-1">|</span>
                                <span class="text-slate-500">{{ $sale->customer->phone }}</span>
                            @else
                                <span class="text-slate-400 italic">No customer</span>
                            @endif
                        </td>
                        <td class="font-medium">{{ money($sale->subtotal) }}</td>
                        <td>
                            <div class="flex flex-col gap-0.5">
                                <span>{{ money($sale->paid) }}</span>
                                @if ($sale->due != 0)
                                    <span class="badge badge-danger w-fit">Due {{ money($sale->due) }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="text-slate-600">
                            @if ($sale->dining_table_id)
                                <span class="badge badge-light">Table {{ $sale->dining_table_id }}</span>
                            @endif
                            @if ($sale->seller_employee_id && $sale->waiter)
                                <span class="text-xs text-slate-500 ml-1">W: {{ $sale->waiter->name }}</span>
                            @endif
                        </td>
                        <td class="text-right">
                            <div class="inline-flex items-center gap-1">
                                <a href="{{ route('seller.sales.invoice', $sale->order_id) }}" class="btn btn-primary btn-sm" title="Invoice" target="_blank">
                                    <i class="ri-printer-line"></i>
                                </a>
                                @if ($sale->due > 0)
                                    <a href="{{ route('seller.sales.mark-paid', $sale->id) }}" class="btn btn-success btn-sm"
                                       onclick="return confirm('Mark this sale as paid?')">
                                        <i class="ri-checkbox-circle-line"></i> Paid
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6">
                        <div class="empty-state">
                            <i class="ri-file-paper-2-line"></i>
                            <h3>No sales yet</h3>
                            <p>Sales will appear here once orders are made.</p>
                        </div>
                    </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($sales->hasPages())
        <div class="card-footer">
            {{ $sales->links() }}
        </div>
    @endif
</div>

@endsection
