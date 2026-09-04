@extends('layouts.admin')
@section('title', 'Branches')
@section('page_title', 'Branches')
@section('breadcrumb')
<a href="{{ route('admin.dashboard') }}">Home</a>
<span class="separator">/</span>
<span class="current">Branches</span>
@endsection

@section('content')

<div class="page-header">
    <div>
        <p class="page-subtitle">Manage restaurant locations. POS, floors, tables, and staff filter by the active branch.</p>
    </div>
    <div class="page-actions">
        <button type="button" class="btn btn-primary" @click="$dispatch('open-modal', { id: 'addBranch' })">
            <i class="ri-add-line"></i> Add Branch
        </button>
    </div>
</div>

<div class="card">
    <div class="table-wrap rounded-t-xl border-0">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Code</th>
                    <th>Contact</th>
                    <th>Floors</th>
                    <th>Tables</th>
                    <th>Staff</th>
                    <th>Status</th>
                    <th class="text-right">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($branches as $branch)
                    <tr>
                        <td class="font-medium text-slate-800">
                            {{ $branch->name }}
                            @if ($branch->is_default)
                                <span class="badge badge-light ml-1">Default</span>
                            @endif
                            @if ((int) active_branch_id() === (int) $branch->id)
                                <span class="badge badge-success ml-1">Active</span>
                            @endif
                        </td>
                        <td>{{ $branch->code ?: '—' }}</td>
                        <td class="text-xs text-slate-500">
                            <div>{{ $branch->phone ?: '—' }}</div>
                            <div class="truncate max-w-xs">{{ $branch->address ?: '' }}</div>
                        </td>
                        <td>{{ $branch->floors_count }}</td>
                        <td>{{ $branch->tables_count }}</td>
                        <td>{{ $branch->employees_count }}</td>
                        <td>
                            <span class="badge {{ $branch->is_active ? 'badge-success' : 'badge-light' }}">
                                {{ $branch->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="text-right space-x-1">
                            <form action="{{ route('admin.branches.switch') }}" method="post" class="inline">
                                @csrf
                                <input type="hidden" name="branch_id" value="{{ $branch->id }}">
                                <button type="submit" class="btn btn-secondary btn-sm" @if((int) active_branch_id() === (int) $branch->id) disabled @endif>
                                    Use
                                </button>
                            </form>
                            <button class="btn btn-primary btn-sm"
                                    @click="$dispatch('open-modal', { id: 'editBranch', branch: {{ json_encode([
                                        'id' => $branch->id,
                                        'name' => $branch->name,
                                        'code' => $branch->code,
                                        'address' => $branch->address,
                                        'phone' => $branch->phone,
                                        'is_active' => $branch->is_active,
                                        'is_default' => $branch->is_default,
                                    ]) }} })">
                                <i class="ri-edit-box-line"></i>
                            </button>
                            <form action="{{ route('admin.branches.destroy', $branch) }}" method="post" class="inline"
                                  onsubmit="return confirm('Delete this branch? Floors/tables/staff will be unassigned.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm"><i class="ri-delete-bin-line"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-slate-500 py-8">
                            No branches yet. Create one if you operate multiple locations.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Add --}}
<div x-data="{ open: false }"
     @open-modal.window="if ($event.detail.id === 'addBranch') open = true"
     x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-lg p-5" @click.outside="open = false">
        <h3 class="text-lg font-semibold mb-4">Add Branch</h3>
        <form method="post" action="{{ route('admin.branches.store') }}" class="space-y-3">
            @csrf
            <div>
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="form-label">Code</label>
                    <input type="text" name="code" class="form-control" placeholder="MAIN">
                </div>
                <div>
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control">
                </div>
            </div>
            <div>
                <label class="form-label">Address</label>
                <input type="text" name="address" class="form-control">
            </div>
            <label class="inline-flex items-center gap-2 text-sm">
                <input type="checkbox" name="is_default" value="1" class="rounded">
                Set as default branch
            </label>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" class="btn btn-secondary" @click="open = false">Cancel</button>
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>

{{-- Edit --}}
<div x-data="{ open: false, branch: {} }"
     @open-modal.window="if ($event.detail.id === 'editBranch') { branch = $event.detail.branch; open = true }"
     x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-lg p-5" @click.outside="open = false">
        <h3 class="text-lg font-semibold mb-4">Edit Branch</h3>
        <form method="post" :action="`{{ url('seller/branches') }}/${branch.id}`" class="space-y-3">
            @csrf
            @method('PUT')
            <div>
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-control" x-model="branch.name" required>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="form-label">Code</label>
                    <input type="text" name="code" class="form-control" x-model="branch.code">
                </div>
                <div>
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control" x-model="branch.phone">
                </div>
            </div>
            <div>
                <label class="form-label">Address</label>
                <input type="text" name="address" class="form-control" x-model="branch.address">
            </div>
            <label class="inline-flex items-center gap-2 text-sm">
                <input type="checkbox" name="is_active" value="1" x-model="branch.is_active" class="rounded">
                Active
            </label>
            <label class="inline-flex items-center gap-2 text-sm">
                <input type="checkbox" name="is_default" value="1" x-model="branch.is_default" class="rounded">
                Default branch
            </label>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" class="btn btn-secondary" @click="open = false">Cancel</button>
                <button type="submit" class="btn btn-primary">Update</button>
            </div>
        </form>
    </div>
</div>

@endsection

