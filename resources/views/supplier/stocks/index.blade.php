@extends('layouts.admin')
@section('title', 'Stock History')
@section('page_title', 'Stock History')
@section('breadcrumb')
<a href="{{ route('supplier.dashboard') }}">Home</a>
<span class="separator">/</span>
<span class="current">Stock History</span>
@endsection

@section('content')

<div class="page-header">
    <div>
        <p class="page-subtitle">{{ $stocks->total() }} {{ Str::plural('movement', $stocks->total()) }} recorded</p>
    </div>
    <div class="page-actions">
        <a class="btn btn-primary" href="{{ route('supplier.stocks.create') }}">
            <i class="ri-add-line"></i> Update Stock
        </a>
    </div>
</div>

<div class="card">
    <div class="table-wrap rounded-t-xl border-0">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Buying Price</th>
                    <th>Selling Price</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($stocks as $stock)
                    <tr>
                        <td class="text-slate-600">{{ $stock->created_at->format('d M Y, h:i A') }}</td>
                        <td>
                            <div class="flex items-center gap-3">
                                <img src="{{ asset('storage/' . $stock->product->image) }}" alt="image" class="h-10 w-10 object-cover rounded-lg" />
                                <span class="font-medium text-slate-800">{{ $stock->product->name }}</span>
                            </div>
                        </td>
                        <td>
                            @if ($stock->type == 'increment')
                                <span class="text-emerald-600 font-semibold inline-flex items-center gap-1">
                                    <i class="ri-arrow-up-line"></i> {{ $stock->quantity }}
                                </span>
                            @else
                                <span class="text-red-600 font-semibold inline-flex items-center gap-1">
                                    <i class="ri-arrow-down-line"></i> {{ $stock->quantity }}
                                </span>
                            @endif
                        </td>
                        <td class="font-medium">{{ money($stock->buying_price) }}</td>
                        <td class="font-medium">{{ money($stock->selling_price) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5">
                        <div class="empty-state">
                            <i class="ri-stock-line"></i>
                            <h3>No stock movements yet</h3>
                            <p>Stock changes will appear here once recorded.</p>
                        </div>
                    </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($stocks->hasPages())
        <div class="card-footer">
            {{ $stocks->links() }}
        </div>
    @endif
</div>

@endsection
