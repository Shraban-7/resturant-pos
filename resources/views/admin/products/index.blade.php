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
        <p class="page-subtitle">{{ $products->total() }} {{ Str::plural('product', $products->total()) }} in your catalog</p>
    </div>
    <div class="page-actions">
        <a class="btn btn-primary" href="{{ route('admin.products.create') }}">
            <i class="ri-add-line"></i> Add Product
        </a>
    </div>
</div>

<div class="card">
    <div class="table-wrap rounded-t-xl border-0">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Current Stock</th>
                    <th>Total Sold</th>
                    <th class="text-right">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($products as $product)
                    <tr>
                        <td>
                            <div class="flex items-center gap-3">
                                <img src="{{ asset('storage/' . $product->image) }}" alt="image" class="h-12 w-12 object-cover rounded-lg" />
                                <span class="font-medium text-slate-800">{{ $product->name }}</span>
                            </div>
                        </td>
                        <td><span class="badge badge-light">{{ $product->category->name }}</span></td>
                        <td class="font-medium">{{ money($product->selling_price) }}</td>
                        <td>{{ $product->availableStock }}</td>
                        <td>{{ $product->stock_out }}</td>
                        <td class="text-right">
                            <div class="inline-flex items-center gap-1">
                                <a href="{{ route('admin.products.recipe.edit', $product) }}" class="btn btn-secondary btn-sm" title="Recipe BOM">
                                    <i class="ri-flask-line"></i>
                                </a>
                                <a href="{{ route('admin.products.modifiers.index', $product) }}" class="btn btn-secondary btn-sm" title="Modifiers">
                                    <i class="ri-list-settings-line"></i>
                                </a>
                                <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-primary btn-sm" title="Edit">
                                    <i class="ri-edit-box-line"></i>
                                </a>
                                <a href="{{ route('admin.products.delete', $product->id) }}"
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
                            <p>Start by adding your first product to the catalog.</p>
                            <a href="{{ route('admin.products.create') }}" class="btn btn-primary mt-4">
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

