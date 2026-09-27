@extends('layouts.admin')
@section('title', 'Roles & Permissions')
@section('page_title', 'Roles & Permissions')
@section('breadcrumb')
<a href="{{ route('admin.dashboard') }}">Home</a>
<span class="separator">/</span>
<span class="current">Roles & Permissions</span>
@endsection

@section('content')

<div class="page-header">
    <div>
        <p class="page-subtitle">{{ $roles->total() }} {{ Str::plural('role', $roles->total()) }} · assign a role to employees, then add extras per employee if needed</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.employees.index') }}" class="btn btn-secondary">
            <i class="ri-user-star-line"></i> Employees
        </a>
        <form action="{{ route('admin.roles.seedDefaults') }}" method="post" class="inline">
            @csrf
            <button type="submit" class="btn btn-secondary" title="Restore Manager / Cashier / Waiter / Chef">
                <i class="ri-refresh-line"></i> Restore defaults
            </button>
        </form>
        <button type="button" class="btn btn-primary" @click="$dispatch('open-modal', { id: 'addRole' })">
            <i class="ri-add-line"></i> Add Role
        </button>
    </div>
</div>

<div class="card">
    <div class="table-wrap rounded-t-xl border-0">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Role</th>
                    <th>Employees</th>
                    <th>Permissions</th>
                    <th>Status</th>
                    <th class="text-right">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($roles as $role)
                    <tr class="{{ ! $role->is_active ? 'opacity-60' : '' }}">
                        <td>
                            <div class="font-medium text-slate-800 flex items-center gap-2">
                                {{ $role->name }}
                                @if ($role->is_system)
                                    <span class="badge badge-light">System</span>
                                @endif
                            </div>
                            <div class="text-xs text-slate-400">{{ $role->slug }}</div>
                        </td>
                        <td>
                            <span class="badge badge-light">{{ $role->users_count }} {{ Str::plural('employee', $role->users_count) }}</span>
                        </td>
                        <td class="text-xs text-slate-500 max-w-md">
                            @php $perms = $role->permissions ?? []; @endphp
                            <span class="badge {{ count($perms) ? 'badge-success' : 'badge-light' }}">{{ count($perms) }} permissions</span>
                            @if (count($perms))
                                <div class="mt-1 flex flex-wrap gap-1">
                                    @foreach (array_slice($perms, 0, 8) as $perm)
                                        <span class="badge badge-light">{{ $perm }}</span>
                                    @endforeach
                                    @if (count($perms) > 8)
                                        <span class="text-slate-400">+{{ count($perms) - 8 }} more</span>
                                    @endif
                                </div>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $role->is_active ? 'badge-success' : 'badge-light' }}">
                                {{ $role->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="text-right">
                            <div class="inline-flex items-center gap-1 justify-end">
                                <button class="btn btn-primary btn-sm" title="Edit" @click="$dispatch('open-modal', { id: 'editRole-{{ $role->id }}' })">
                                    <i class="ri-edit-box-line"></i>
                                </button>
                                <form action="{{ route('admin.roles.toggleStatus', $role) }}" method="post" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-secondary btn-sm" title="{{ $role->is_active ? 'Deactivate' : 'Activate' }}">
                                        <i class="{{ $role->is_active ? 'ri-pause-circle-line' : 'ri-play-circle-line' }}"></i>
                                    </button>
                                </form>
                                <form action="{{ route('admin.roles.destroy', $role) }}" method="post" class="inline"
                                      onsubmit="return confirm('Delete role {{ addslashes($role->name) }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" title="Delete" @if($role->is_system || $role->users_count) disabled @endif>
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>

                    <div x-data="{ open: false }" @open-modal.window="if ($event.detail.id === 'editRole-{{ $role->id }}') open = true" @keydown.escape.window="open = false">
                        <template x-teleport="body">
                            <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none">
                                <div class="modal-backdrop" @click="open = false"></div>
                                <div class="modal-dialog relative !max-w-2xl">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit Role — {{ $role->name }}</h5>
                                            <button type="button" class="text-slate-500 hover:text-slate-800" @click="open = false" aria-label="Close">
                                                <i class="ri-close-line text-xl"></i>
                                            </button>
                                        </div>
                                        <form action="{{ route('admin.roles.update', $role) }}" method="post">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-body space-y-4 max-h-[70vh] overflow-y-auto">
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                                    <div class="form-group">
                                                        <label class="form-label">Role name *</label>
                                                        <input name="name" type="text" class="form-control" value="{{ $role->name }}" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="form-label">Status</label>
                                                        <select name="is_active" class="form-control">
                                                            <option value="1" @selected($role->is_active)>Active</option>
                                                            <option value="0" @selected(! $role->is_active)>Inactive</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="form-label">Permissions</label>
                                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                                        @foreach ($groups as $groupName => $groupPerms)
                                                            <div class="rounded-lg border border-slate-200 p-3">
                                                                <div class="flex items-center justify-between mb-2">
                                                                    <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $groupName }}</div>
                                                                    <button type="button" class="text-xs text-brand-600 hover:underline"
                                                                        @click="$el.closest('.rounded-lg').querySelectorAll('input[type=checkbox]').forEach(c => c.checked = true)">All</button>
                                                                </div>
                                                                @foreach ($groupPerms as $permKey => $permLabel)
                                                                    <label class="flex items-center gap-2 text-sm py-0.5">
                                                                        <input type="checkbox" name="permissions[]" value="{{ $permKey }}" @checked(in_array($permKey, $role->permissions ?? []))>
                                                                        <span title="{{ $permKey }}">{{ $permLabel }}</span>
                                                                    </label>
                                                                @endforeach
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" @click="open = false">Cancel</button>
                                                <button type="submit" class="btn btn-primary">Update</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                @empty
                    <tr><td colspan="5">
                        <div class="empty-state">
                            <i class="ri-shield-user-line"></i>
                            <h3>No roles yet</h3>
                            <p>Create roles like Manager, Cashier, Waiter — then assign them to employees.</p>
                        </div>
                    </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($roles->hasPages())
        <div class="card-footer">
            {{ $roles->links() }}
        </div>
    @endif
</div>

<div x-data="{ open: false }" @open-modal.window="if ($event.detail.id === 'addRole') open = true" @keydown.escape.window="open = false">
    <template x-teleport="body">
        <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none">
            <div class="modal-backdrop" @click="open = false"></div>
            <div class="modal-dialog relative !max-w-2xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Role</h5>
                        <button type="button" class="text-slate-500 hover:text-slate-800" @click="open = false" aria-label="Close">
                            <i class="ri-close-line text-xl"></i>
                        </button>
                    </div>
                    <form action="{{ route('admin.roles.store') }}" method="post">
                        @csrf
                        <div class="modal-body space-y-4 max-h-[70vh] overflow-y-auto">
                            <div class="form-group">
                                <label class="form-label">Role name *</label>
                                <input name="name" type="text" class="form-control" placeholder="e.g. Cashier" required>
                            </div>
                            @if (! empty($defaults))
                                <div class="text-xs text-slate-500">
                                    Quick start:
                                    @foreach ($defaults as $name => $perms)
                                        <span class="badge badge-light mr-1">{{ $name }} ({{ count($perms) }})</span>
                                    @endforeach
                                    — use “Restore defaults” to create them all.
                                </div>
                            @endif
                            <div class="form-group">
                                <label class="form-label">Permissions</label>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    @foreach ($groups as $groupName => $groupPerms)
                                        <div class="rounded-lg border border-slate-200 p-3">
                                            <div class="flex items-center justify-between mb-2">
                                                <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $groupName }}</div>
                                                <button type="button" class="text-xs text-brand-600 hover:underline"
                                                    @click="$el.closest('.rounded-lg').querySelectorAll('input[type=checkbox]').forEach(c => c.checked = true)">All</button>
                                            </div>
                                            @foreach ($groupPerms as $permKey => $permLabel)
                                                <label class="flex items-center gap-2 text-sm py-0.5">
                                                    <input type="checkbox" name="permissions[]" value="{{ $permKey }}">
                                                    <span title="{{ $permKey }}">{{ $permLabel }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" @click="open = false">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save Role</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </template>
</div>

@endsection
