@extends('layouts.admin')
@section('title', 'Additions')
@section('page_title', 'Additions')
@section('breadcrumb')
<a href="{{ route('admin.dashboard') }}">Home</a>
<span class="separator">/</span>
<span class="current">Additions</span>
@endsection

@section('content')

<div class="page-header">
    <div>
        <p class="page-subtitle">Priced extras guests can add to any dish — e.g. Cheese, Extra Sauce</p>
    </div>
    <div class="page-actions">
        <button type="button" class="btn btn-primary" @click="$dispatch('open-modal', { id: 'addAddition' })">
            <i class="ri-add-line"></i> Add Addition
        </button>
    </div>
</div>

<div class="card">
    <div class="table-wrap rounded-t-xl border-0">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Name</th>
                    <th class="text-right">Price</th>
                    <th class="text-right">Dishes</th>
                    <th class="text-right">Status</th>
                    <th class="text-right">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($additions as $addition)
                    <tr>
                        <td class="font-medium text-slate-800">{{ $addition->name }}</td>
                        <td class="text-right">{{ money($addition->price) }}</td>
                        <td class="text-right">{{ $addition->products_count }}</td>
                        <td class="text-right">
                            <span class="badge {{ $addition->is_active ? 'badge-success' : 'badge-light' }}">
                                {{ $addition->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="text-right space-x-1">
                            <button class="btn btn-primary btn-sm"
                                    @click="$dispatch('open-modal', { id: 'editAddition', addition: {{ json_encode(['id' => $addition->id, 'name' => $addition->name, 'price' => (float) $addition->price, 'is_active' => (bool) $addition->is_active]) }} })">
                                <i class="ri-edit-box-line"></i>
                            </button>
                            <form action="{{ route('admin.additions.destroy', $addition) }}" method="post" class="inline"
                                  onsubmit="return confirm('Remove this addition? It will be detached from all dishes.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm"><i class="ri-delete-bin-line"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-slate-500 py-8">
                            No additions yet. Add priced extras like Cheese or Extra Chicken.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($additions->hasPages())
        <div class="card-footer">{{ $additions->links() }}</div>
    @endif
</div>

{{-- Add --}}
<div x-data="{ open: false }"
     @open-modal.window="if ($event.detail.id === 'addAddition') open = true"
     x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-lg p-5" @click.outside="open = false">
        <h3 class="text-lg font-semibold mb-4">Add Addition</h3>
        <form method="post" action="{{ route('admin.additions.store') }}" class="space-y-3">
            @csrf
            <div>
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-control" placeholder="e.g. Extra Cheese" required>
            </div>
            <div>
                <label class="form-label">Price</label>
                <input type="number" name="price" class="form-control" min="0" step="0.01" value="0" required>
            </div>
            <label class="inline-flex items-center gap-2 text-sm">
                <input type="checkbox" name="is_active" value="1" checked class="rounded border-slate-300"> Active
            </label>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" class="btn btn-secondary" @click="open = false">Cancel</button>
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>

{{-- Edit --}}
<div x-data="{ open: false, addition: {} }"
     @open-modal.window="if ($event.detail.id === 'editAddition') { addition = $event.detail.addition; open = true }"
     x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-lg p-5" @click.outside="open = false">
        <h3 class="text-lg font-semibold mb-4">Edit Addition</h3>
        <form method="post" :action="`{{ url('admin/additions') }}/${addition.id}`" class="space-y-3">
            @csrf
            @method('PUT')
            <div>
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-control" x-model="addition.name" required>
            </div>
            <div>
                <label class="form-label">Price</label>
                <input type="number" name="price" class="form-control" min="0" step="0.01" x-model="addition.price" required>
            </div>
            <label class="inline-flex items-center gap-2 text-sm">
                <input type="checkbox" name="is_active" value="1" class="rounded border-slate-300" :checked="addition.is_active"> Active
            </label>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" class="btn btn-secondary" @click="open = false">Cancel</button>
                <button type="submit" class="btn btn-primary">Update</button>
            </div>
        </form>
    </div>
</div>

@endsection
