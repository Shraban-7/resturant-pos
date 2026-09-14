@extends('layouts.admin')
@section('title', __('admin.sales.title'))
@section('page_title', __('admin.sales.title'))
@section('breadcrumb')
<a href="{{ route('admin.dashboard') }}">{{ __('admin.common.home') }}</a>
<span class="separator">/</span>
<span class="current">{{ __('admin.sales.breadcrumb_sales') }}</span>
@endsection

@section('content')

<div class="page-header">
    <div>
        <p class="page-subtitle">{{ __('admin.sales.total_sales') }} <span class="font-semibold text-brand-600">{{ money($totalSales) }}</span></p>
    </div>
</div>

<div class="card">
    <div class="table-wrap rounded-t-xl border-0">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>{{ __('admin.sales.date') }}</th>
                    <th>{{ __('admin.sales.customer_phone') }}</th>
                    <th>{{ __('admin.sales.total') }}</th>
                    <th>{{ __('admin.sales.paid') }}</th>
                    <th>{{ __('admin.sales.table_waiter') }}</th>
                    <th class="text-right">{{ __('admin.sales.action') }}</th>
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
                                <span class="text-slate-400 italic">{{ __('admin.sales.no_customer') }}</span>
                            @endif
                        </td>
                        <td class="font-medium">{{ money($sale->subtotal) }}</td>
                        <td>
                            <div class="flex flex-col gap-0.5">
                                <span>{{ money($sale->paid) }}</span>
                                @if ($sale->due != 0)
                                    <span class="badge badge-danger w-fit">{{ __('admin.sales.due') }} {{ money($sale->due) }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="text-slate-600">
                            @if ($sale->dining_table_id)
                                <span class="badge badge-light">{{ __('admin.sales.table') }} {{ $sale->dining_table_id }}</span>
                            @endif
                            @if ($sale->employee_id && $sale->waiter)
                                <span class="text-xs text-slate-500 ml-1">W: {{ $sale->waiter->name }}</span>
                            @endif
                        </td>
                        <td class="text-right">
                            <div class="inline-flex items-center gap-1">
                                <a href="{{ route('admin.sales.invoice', $sale->order_id) }}" class="btn btn-primary btn-sm" title="{{ __('admin.sales.invoice') }}" target="_blank">
                                    <i class="ri-printer-line"></i>
                                </a>
                                @if ($sale->due > 0)
                                    <a href="{{ route('admin.sales.mark-paid', $sale->id) }}" class="btn btn-success btn-sm"
                                       onclick="return confirm('{{ __('admin.sales.confirm_mark_paid') }}')">
                                        <i class="ri-checkbox-circle-line"></i> {{ __('admin.sales.mark_as_paid') }}
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6">
                        <div class="empty-state">
                            <i class="ri-file-paper-2-line"></i>
                            <h3>{{ __('admin.sales.no_sales') }}</h3>
                            <p>{{ __('admin.sales.no_sales_desc') }}</p>
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


