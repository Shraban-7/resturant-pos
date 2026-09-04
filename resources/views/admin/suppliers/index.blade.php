@extends('layouts.admin')
@section('title', 'Suppliers')
@section('page_title', 'Suppliers')
@section('breadcrumb')
<a href="{{ route('admin.dashboard') }}">Home</a>
<span class="separator">/</span>
<span class="current">Suppliers</span>
@endsection

@section('content')

<div class="page-header">
    <div>
        <p class="page-subtitle">Buy raw ingredients from these suppliers. Purchases update stock automatically.</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.purchases.index') }}" class="btn btn-secondary"><i class="ri-shopping-basket-line"></i> Purchases</a>
        <button type="button" class="btn btn-primary" @click="$dispatch('open-modal', { id: 'addSupplier' })">
            <i class="ri-add-line"></i> Add Supplier
        </button>
    </div>
</div>

<div class="card">
    <div class="table-wrap rounded-t-xl border-0">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Contact</th>
                    <th class="text-right">Purchases</th>
                    <th>Status</th>
                    <th class="text-right">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($suppliers as $supplier)
                    <tr>
                        <td class="font-medium text-slate-800">{{ $supplier->name }}</td>
                        <td class="text-xs text-slate-500">
                            <div>{{ $supplier->phone ?: '-' }}</div>
                            <div class="truncate max-w-xs">{{ $supplier->address ?: '' }}</div>
                        </td>
                        <td class="text-right">{{ $supplier->purchases_count }}</td>
                        <td>
                            <span class="badge {{ $supplier->is_active ? 'badge-success' : 'badge-light' }}">
                                {{ $supplier->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="text-right space-x-1">
                            <button class="btn btn-primary btn-sm"
                                    @click="$dispatch('open-modal', { id: 'editSupplier', supplier: {{ json_encode([
                                        'id' => $supplier->id,
                                        'name' => $supplier->name,
                                        'phone' => $supplier->phone,
                                        'address' => $supplier->address,
                                        'is_active' => $supplier->is_active,
                                    ]) }} })">
                                <i class="ri-edit-box-line"></i>
                            </button>
                            <form action="{{ route('admin.suppliers.destroy', $supplier) }}" method="post" class="inline"
                                  onsubmit="return confirm('Remove this supplier? Purchase history will be deleted.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm"><i class="ri-delete-bin-line"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-slate-500 py-8">
                            No suppliers yet. Add the wholesalers you buy raw ingredients from.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($suppliers->hasPages())
        <div class="card-footer">{{ $suppliers->links() }}</div>
    @endif
</div>

{{-- Add --}}
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
            <label class="inline-flex items-center gap-2 text-sm">
                <input type="checkbox" name="is_active" value="1" class="rounded" checked>
                Active
            </label>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" class="btn btn-secondary" @click="open = false">Cancel</button>
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>

{{-- Edit --}}
<div x-data="{ open: false, supplier: {} }"
     @open-modal.window="if ($event.detail.id === 'editSupplier') { supplier = $event.detail.supplier; open = true }"
     x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-lg p-5" @click.outside="open = false">
        <h3 class="text-lg font-semibold mb-4">Edit Supplier</h3>
        <form method="post" :action="`{{ url('admin/suppliers') }}/${supplier.id}`" class="space-y-3">
            @csrf
            @method('PUT')
            <div>
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-control" x-model="supplier.name" required>
            </div>
            <div>
                <label class="form-label">Phone</label>
                <input type="text" name="phone" class="form-control" x-model="supplier.phone">
            </div>
            <div>
                <label class="form-label">Address</label>
                <input type="text" name="address" class="form-control" x-model="supplier.address">
            </div>
            <label class="inline-flex items-center gap-2 text-sm">
                <input type="checkbox" name="is_active" value="1" x-model="supplier.is_active" class="rounded">
                Active
            </label>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" class="btn btn-secondary" @click="open = false">Cancel</button>
                <button type="submit" class="btn btn-primary">Update</button>
            </div>
        </form>
    </div>
</div>

@endsection
