@extends('layouts.admin')
@section('title', 'Customers')
@section('page_title', 'Customers')
@section('breadcrumb')
<a href="{{ route('admin.dashboard') }}">Home</a>
<span class="separator">/</span>
<span class="current">Customers</span>
@endsection

@section('content')

<div class="page-header">
    <div>
        <p class="page-subtitle">{{ $customers->total() }} {{ Str::plural('customer', $customers->total()) }} in your book</p>
    </div>
    <div class="page-actions">
        <button type="button" class="btn btn-primary" @click="$dispatch('open-modal', { id: 'customerModal' })">
            <i class="ri-add-line"></i> Add Customer
        </button>
    </div>
</div>

<div class="card">
    <div class="table-wrap rounded-t-xl border-0">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Phone</th>
                    <th>Address</th>
                    <th>Customer Since</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($customers as $customer)
                    <tr>
                        <td class="text-slate-500">#{{ $customer->id }}</td>
                        <td class="font-medium text-slate-800">{{ $customer->name }}</td>
                        <td>{{ $customer->phone }}</td>
                        <td class="text-slate-600">{{ $customer->address }}</td>
                        <td class="text-slate-500">{{ $customer->created_at->format('d M Y') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5">
                        <div class="empty-state">
                            <i class="ri-team-line"></i>
                            <h3>No customers yet</h3>
                            <p>Add your first customer to get started.</p>
                        </div>
                    </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($customers->hasPages())
        <div class="card-footer">
            {{ $customers->links() }}
        </div>
    @endif
</div>

<div x-data="{ open: false }" @open-modal.window="if ($event.detail.id === 'customerModal') open = true" @keydown.escape.window="open = false">
    <template x-teleport="body">
        <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none">
            <div class="modal-backdrop" @click="open = false"></div>
            <div class="modal-dialog relative">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title">Add New Customer</h1>
                        <button type="button" class="text-slate-500 hover:text-slate-800" @click="open = false" aria-label="Close">
                            <i class="ri-close-line text-xl"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('admin.customers.store') }}" method="POST">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="form-label">Name</label>
                                    <input type="text" name="name" class="form-control" required>
                                </div>
                                <div>
                                    <label class="form-label">Phone</label>
                                    <input type="text" name="phone" class="form-control" required>
                                </div>
                                <div class="md:col-span-2">
                                    <label class="form-label">Address</label>
                                    <textarea name="address" class="form-control" rows="2"></textarea>
                                </div>
                            </div>
                            <div class="flex justify-end gap-2 mt-5">
                                <button type="button" class="btn btn-secondary" @click="open = false">Cancel</button>
                                <button type="submit" class="btn btn-primary">Save Customer</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>

@endsection

