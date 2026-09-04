@extends('layouts.admin')
@section('title', 'Add Product')
@section('page_title', 'Add Product')
@section('breadcrumb')
<a href="{{ route('admin.products.index') }}">Products</a>
<span class="separator">/</span>
<span class="current">Add</span>
@endsection

@section('content')

<form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="lg:col-span-2">
            <div class="card">
                <div class="card-header">
                    <div>
                        <h6 class="card-title">Product Information</h6>
                        <p class="card-subtitle">Add the basic details of your new product</p>
                    </div>
                </div>
                <div class="card-body">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">Category</label>
                            <select name="category_id" class="form-select" required>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Name</label>
                            <input type="text" name="name" placeholder="Enter item name" class="form-control" required>
                        </div>

                        <div>
                            <label class="form-label">Unit</label>
                            <select name="unit_id" class="form-select" required>
                                @foreach ($units as $unit)
                                    <option value="{{ $unit->id }}">{{ $unit->name }} ({{ $unit->short_name }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Buying Price</label>
                            <div class="input-group">
                                <span class="input-group-text">BDT</span>
                                <input type="text" name="buying_price" class="form-control" placeholder="1000" required>
                            </div>
                        </div>

                        <div>
                            <label class="form-label">Selling Price</label>
                            <div class="input-group">
                                <span class="input-group-text">BDT</span>
                                <input type="text" name="selling_price" class="form-control" placeholder="1500" required>
                            </div>
                        </div>
                        <div>
                            <label class="form-label">Opening Stock</label>
                            <input type="text" name="stock_in" class="form-control" placeholder="Initial stock" required>
                        </div>

                        <div class="md:col-span-2">
                            <label class="form-label">Image <span class="text-slate-500 text-xs">(optional)</span></label>
                            <input class="form-control" type="file" name="image">
                            <p class="form-hint">Recommended: 500x500px or 1000x1000px square, PNG/JPEG.</p>
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
                        <i class="ri-save-line"></i> Save Product
                    </button>
                    <a href="{{ route('admin.products.index') }}" class="btn btn-secondary w-full">
                        <i class="ri-close-line"></i> Cancel
                    </a>
                </div>
            </div>
        </div>
    </div>
</form>

@endsection

