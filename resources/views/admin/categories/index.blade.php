@extends('layouts.admin')
@section('title', 'Categories')
@section('page_title', 'Categories')
@section('breadcrumb')
<a href="{{ route('admin.dashboard') }}">Home</a>
<span class="separator">/</span>
<span class="current">Categories</span>
@endsection

@section('content')

<div class="page-header">
    <div>
        <p class="page-subtitle">Group menu items and products · shared by both sections</p>
    </div>
    <div class="page-actions">
        <button type="button" class="btn btn-primary" @click="$dispatch('open-modal', { id: 'addCategory' })">
            <i class="ri-add-line"></i> Add Category
        </button>
    </div>
</div>

<div class="card">
    <div class="table-wrap rounded-t-xl border-0">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Name</th>
                    <th class="text-right">Products</th>
                    <th class="text-right">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($categories as $category)
                    <tr>
                        <td class="font-medium text-slate-800">{{ $category->name }}</td>
                        <td class="text-right">{{ $category->products_count }}</td>
                        <td class="text-right space-x-1">
                            <button class="btn btn-primary btn-sm"
                                    @click="$dispatch('open-modal', { id: 'editCategory', category: {{ json_encode(['id' => $category->id, 'name' => $category->name]) }} })">
                                <i class="ri-edit-box-line"></i>
                            </button>
                            <form action="{{ route('admin.categories.destroy', $category) }}" method="post" class="inline"
                                  onsubmit="return confirm('Remove this category?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm"><i class="ri-delete-bin-line"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center text-slate-500 py-8">
                            No categories yet. Add cuisines or material groups to organize your catalog.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($categories->hasPages())
        <div class="card-footer">{{ $categories->links() }}</div>
    @endif
</div>

{{-- Add --}}
<div x-data="{ open: false }"
     @open-modal.window="if ($event.detail.id === 'addCategory') open = true"
     x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-lg p-5" @click.outside="open = false">
        <h3 class="text-lg font-semibold mb-4">Add Category</h3>
        <form method="post" action="{{ route('admin.categories.store') }}" class="space-y-3">
            @csrf
            <div>
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-control" placeholder="e.g. Bengali Food, Beverages, Spices" required>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" class="btn btn-secondary" @click="open = false">Cancel</button>
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>

{{-- Edit --}}
<div x-data="{ open: false, category: {} }"
     @open-modal.window="if ($event.detail.id === 'editCategory') { category = $event.detail.category; open = true }"
     x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-lg p-5" @click.outside="open = false">
        <h3 class="text-lg font-semibold mb-4">Edit Category</h3>
        <form method="post" :action="`{{ url('admin/categories') }}/${category.id}`" class="space-y-3">
            @csrf
            @method('PUT')
            <div>
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-control" x-model="category.name" required>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" class="btn btn-secondary" @click="open = false">Cancel</button>
                <button type="submit" class="btn btn-primary">Update</button>
            </div>
        </form>
    </div>
</div>

@endsection
