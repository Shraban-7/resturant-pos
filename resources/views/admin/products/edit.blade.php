@extends('layouts.admin')
@section('title', 'Edit Product')
@section('page_title', 'Edit Product')
@section('breadcrumb')
<a href="{{ route('admin.products.index') }}">Products</a>
<span class="separator">/</span>
<span class="current">Edit</span>
@endsection

@section('content')

<form action="{{ route('admin.products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
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
                            <label class="form-label">Category</label>
                            <select name="category_id" class="form-select" required>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" {{ $category->id == $product->category_id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Name</label>
                            <input type="text" name="name" class="form-control" value="{{ $product->name }}" required>
                        </div>
                        <div>
                            <label class="form-label">Type</label>
                            <select name="type" class="form-select">
                                <option value="dish" @selected($product->type === \App\Enums\ProductType::DISH)>Dish (sellable, stock tracked)</option>
                                <option value="buffet" @selected($product->type === \App\Enums\ProductType::BUFFET)>Buffet (per person, unlimited)</option>
                                <option value="ingredient" @selected($product->type === \App\Enums\ProductType::INGREDIENT)>Raw ingredient (recipe use only, never sold)</option>
                            </select>
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
                            <label class="form-label">Buying Price</label>
                            <div class="input-group">
                                <span class="input-group-text">BDT</span>
                                <input type="text" name="buying_price" class="form-control" value="{{ $product->buying_price }}">
                            </div>
                        </div>

                        <div>
                            <label class="form-label">Selling Price</label>
                            <div class="input-group">
                                <span class="input-group-text">BDT</span>
                                <input type="text" name="selling_price" class="form-control" value="{{ $product->selling_price }}" required>
                            </div>
                        </div>
                        <div>
                            <label class="form-label">Opening Stock</label>
                            <input type="text" name="stock_in" class="form-control" value="{{ $product->stock_in }}" required>
                        </div>

                        <div class="md:col-span-2">
                            <label class="form-label">Served at <span class="text-slate-500 text-xs">(all ticked or unticked = all day)</span></label>
                            <div class="flex flex-wrap gap-4">
                                @foreach (\App\Enums\MealSlot::values() as $slot)
                                    <label class="flex items-center gap-1.5 text-sm text-slate-700">
                                        <input type="checkbox" name="meal_times[]" value="{{ $slot }}" class="rounded"
                                            @checked(empty($product->meal_times) || in_array($slot, $product->meal_times ?? []))>
                                        {{ ucfirst($slot) }}
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="md:col-span-2">
                            @if($product->image)
                                <img src="{{ asset('storage/' . $product->image) }}" alt="image" class="h-24 w-24 rounded-lg object-cover mb-3 border border-slate-200">
                            @endif
                            <label class="form-label">Image <span class="text-slate-500 text-xs">(optional)</span></label>
                            <input class="form-control" type="file" name="image">
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
                    <a href="{{ route('admin.products.index') }}" class="btn btn-secondary w-full">
                        <i class="ri-close-line"></i> Cancel
                    </a>
                </div>
            </div>
        </div>
    </div>
</form>

@endsection




