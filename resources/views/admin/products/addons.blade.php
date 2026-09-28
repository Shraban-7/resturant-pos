@extends('layouts.admin')
@section('title', 'Menu Item Add-ons')
@section('page_title', 'Menu Item Add-ons')
@section('breadcrumb')
    <a href="{{ route('admin.dashboard') }}">Home</a>
    <span class="separator">/</span>
    <a href="{{ route('admin.products.index') }}">Menu Items</a>
    <span class="separator">/</span>
    <span class="current">Add-ons</span>
@endsection

@section('content')

    <div class="page-header">
        <div>
            <p class="page-subtitle">Suggest extra items with <strong>{{ $product->name }}</strong> — e.g. Coke with Burger.
                Add-ons sell as their own order lines at POS.</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-secondary">
                <i class="ri-arrow-left-line"></i> Back to Item
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="card">
            <div class="card-header">
                <h3 class="font-semibold text-slate-800">Suggested with this item</h3>
            </div>
            <div class="table-wrap border-0">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Add-on</th>
                            <th class="text-right">Price</th>
                            <th class="text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($product->addons as $addon)
                            <tr>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $addon->imageUrl() }}" alt="image"
                                            class="h-10 w-10 object-cover rounded-lg bg-orange-50" />
                                        <span class="font-medium text-slate-800">{{ $addon->name }}</span>
                                    </div>
                                </td>
                                <td class="text-right font-medium">{{ money($addon->selling_price) }}</td>
                                <td class="text-right">
                                    <form action="{{ route('admin.products.addons.destroy', [$product, $addon]) }}"
                                        method="post" class="inline"
                                        onsubmit="return confirm('Remove this add-on suggestion?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="inline-flex items-center justify-center px-2 py-1 text-sm font-medium text-red-600 border border-red-600 rounded hover:bg-red-600 hover:text-white transition-colors"
                                            title="Remove">
                                            <i class="ri-close-line"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3">
                                    <div class="empty-state py-8">
                                        <i class="ri-add-circle-line"></i>
                                        <h3>No add-ons yet</h3>
                                        <p>Attach extra items guests often order with this one.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="font-semibold text-slate-800">Attach an add-on</h3>
            </div>
            <div class="card-body">
                @if ($availableAddons->isEmpty())
                    <p class="text-sm text-slate-500">No other sellable menu items to suggest, or all are already attached.
                    </p>
                @else
                    <form action="{{ route('admin.products.addons.store', $product) }}" method="post" class="space-y-3">
                        @csrf
                        <div class="form-group">
                            <label class="form-label">Menu item</label>
                            <select name="addon_product_id" class="form-select form-control" required>
                                <option value="">Select item</option>
                                @foreach ($availableAddons as $addon)
                                    <option value="{{ $addon->id }}">
                                        {{ $addon->name }} ({{ money($addon->selling_price) }})
                                    </option>
                                @endforeach
                            </select>
                            <p class="form-hint">Only active dishes/buffets can be suggested. Raw materials never appear
                                here.</p>
                        </div>
                        <div>
                            <button type="submit" class="btn btn-primary">Attach Add-on</button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>

@endsection
