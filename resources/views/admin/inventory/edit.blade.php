@extends('layouts.admin')
@section('title', 'Edit Product')
@section('page_title', 'Edit Product')
@section('breadcrumb')
<a href="{{ route('admin.inventory.index') }}">Products</a>
<span class="separator">/</span>
<span class="current">Edit</span>
@endsection

@section('content')

<form action="{{ route('admin.inventory.update', $product->id) }}" method="POST">
    @csrf
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="lg:col-span-2">
            <div class="card">
                <div class="card-header">
                    <div>
                        <h6 class="card-title">Product Information</h6>
                        <p class="card-subtitle">Update the details for {{ $product->name }}</p>
                    </div>
                </div>
                <div class="card-body">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">Item Name</label>
                            <input type="text" name="name" class="form-control" value="{{ $product->name }}" required>
                        </div>
                        <div>
                            <label class="form-label">Unit</label>
                            <select name="unit_id" class="form-select" required>
                                @foreach ($units as $unit)
                                    <option value="{{ $unit->id }}" {{ $unit->id == $product->unit_id ? 'selected' : '' }}>
                                        {{ $unit->name }} ({{ $unit->short_name }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Supplier</label>
                            <div class="flex gap-2">
                                <select name="supplier_id" class="form-select flex-1">
                                    <option value="">Select supplier (optional)</option>
                                    @foreach ($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}" @selected((int) $product->supplier_id === (int) $supplier->id)>{{ $supplier->name }}</option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn btn-secondary shrink-0" @click="$dispatch('open-modal', { id: 'addSupplier' })" title="Add new supplier">
                                    <i class="ri-add-line"></i>
                                </button>
                            </div>
                        </div>
                        <div>
                            <label class="form-label">Reorder Level</label>
                            <input type="text" name="reorder_level" class="form-control" value="{{ $product->reorder_level ?? 0 }}">
                            <p class="form-hint">Warns as low stock when available falls at or below this.</p>
                        </div>
                        <div>
                            <label class="form-label">Cost / Unit</label>
                            <div class="input-group">
                                <span class="input-group-text">BDT</span>
                                <input type="text" name="buying_price" class="form-control" value="{{ $product->buying_price }}" required>
                            </div>
                        </div>
                        <div>
                            <label class="form-label">Opening Quantity</label>
                            <input type="text" name="stock_in" class="form-control" value="{{ $product->stock_in }}" required>
                            <p class="form-hint">Currently available: {{ $product->stock_in - $product->stock_out }} {{ $product->unit?->short_name }} · used so far: {{ $product->stock_out }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div>
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title">Actions</h6>
                </div>
                <div class="card-body space-y-3">
                    <button type="submit" class="btn btn-primary w-full">
                        <i class="ri-save-line"></i> Update Product
                    </button>
                    <a href="{{ route('admin.inventory.index') }}" class="btn btn-secondary w-full">
                        <i class="ri-close-line"></i> Cancel
                    </a>
                </div>
            </div>
        </div>
    </div>
</form>

{{-- Quick-add supplier (returns here with the new supplier selectable) --}}
<div x-data="{ open: false }"
     @open-modal.window="if ($event.detail.id === 'addSupplier') open = true"
     x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-lg p-5" @click.outside="open = false">
        <h3 class="text-lg font-semibold mb-4">Add Supplier</h3>
        <form method="post" action="{{ route('admin.suppliers.store') }}" class="space-y-3">
            @csrf
            <div>
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div>
                <label class="form-label">Phone</label>
                <input type="text" name="phone" class="form-control">
            </div>
            <div>
                <label class="form-label">Address</label>
                <input type="text" name="address" class="form-control">
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" class="btn btn-secondary" @click="open = false">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Supplier</button>
            </div>
        </form>
    </div>
</div>

@endsection
