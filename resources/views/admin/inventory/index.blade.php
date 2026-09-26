@extends('layouts.admin')
@section('title', 'Products')
@section('page_title', 'Products')
@section('breadcrumb')
<a href="{{ route('admin.dashboard') }}">Home</a>
<span class="separator">/</span>
<span class="current">Products</span>
@endsection

@section('content')

<div class="page-header">
    <div>
        <p class="page-subtitle">{{ $products->total() }} {{ Str::plural('product', $products->total()) }} in inventory · recipes consume these</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.purchases.index') }}" class="btn btn-secondary"><i class="ri-shopping-basket-line"></i> Purchases</a>
        <a class="btn btn-primary" href="{{ route('admin.inventory.create') }}">
            <i class="ri-add-line"></i> Add Product
        </a>
    </div>
</div>

<div class="card">
    <div class="table-wrap rounded-t-xl border-0">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Supplier</th>
                    <th class="text-right">Cost / Unit</th>
                    <th class="text-right">Current Stock</th>
                    <th class="text-right">Reorder Level</th>
                    <th class="text-right">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($products as $product)
                    @php
                        $available = $product->stock_in - $product->stock_out;
                        $reorder = (float) ($product->reorder_level ?? 0);
                        $isLow = $reorder > 0 && $available <= $reorder;
                    @endphp
                    <tr>
                        <td>
                            <div class="flex items-center gap-3">
                                <span class="font-medium text-slate-800">{{ $product->name }}</span>
                                @if ($isLow)
                                    <span class="badge badge-danger" title="At or below reorder level">Low</span>
                                @endif
                            </div>
                            <span class="block text-xs text-slate-500">{{ $product->unit?->name }} ({{ $product->unit?->short_name }})</span>
                        </td>
                        <td class="text-slate-600">{{ $product->supplier?->name ?? '-' }}</td>
                        <td class="text-right font-medium">{{ money($product->buying_price) }}</td>
                        <td class="text-right {{ $isLow ? 'text-red-600 font-semibold' : '' }}">{{ rtrim(rtrim(number_format($available, 3), '0'), '.') }} {{ $product->unit?->short_name }}</td>
                        <td class="text-right text-slate-500">{{ rtrim(rtrim(number_format($reorder, 3), '0'), '.') }}</td>
                        <td class="text-right">
                            <div class="inline-flex items-center gap-1">
                                <a href="{{ route('admin.inventory.edit', $product->id) }}" class="btn btn-primary btn-sm" title="Edit">
                                    <i class="ri-edit-box-line"></i>
                                </a>
                                <a href="{{ route('admin.inventory.delete', $product->id) }}"
                                   onclick="return confirm('Are you sure you want to remove this product?')"
                                   class="btn btn-danger btn-sm" title="Delete">
                                    <i class="ri-delete-bin-7-line"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6">
                        <div class="empty-state">
                            <i class="ri-box-3-line"></i>
                            <h3>No products yet</h3>
                            <p>Add the ingredients your recipes consume, then record purchases.</p>
                            <a href="{{ route('admin.inventory.create') }}" class="btn btn-primary mt-4">
                                <i class="ri-add-line"></i> Add Product
                            </a>
                        </div>
                    </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($products->hasPages())
        <div class="card-footer">
            {{ $products->links() }}
        </div>
    @endif
</div>

@endsection
