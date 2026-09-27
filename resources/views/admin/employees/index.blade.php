@extends('layouts.admin')
@section('title', 'Employees')
@section('page_title', 'Employees')
@section('breadcrumb')
<a href="{{ route('admin.dashboard') }}">Home</a>
<span class="separator">/</span>
<span class="current">Employees</span>
@endsection

@section('content')

<div class="page-header">
    <div>
        <p class="page-subtitle">{{ $employees->total() }} {{ Str::plural('employee', $employees->total()) }} with login access · roles grant base permissions</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">
            <i class="ri-shield-user-line"></i> Roles & Permissions
        </a>
        <button type="button" class="btn btn-primary" @click="$dispatch('open-modal', { id: 'addEmployee' })">
            <i class="ri-add-line"></i> Add Employee
        </button>
    </div>
</div>

<div class="card mb-4">
    <form method="get" action="{{ route('admin.employees.index') }}" class="flex flex-wrap items-end gap-3 p-4">
        <div class="min-w-[200px] flex-1">
            <label class="form-label">Search</label>
            <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Name, email, phone...">
        </div>
        <div>
            <label class="form-label">Role</label>
            <select name="role_id" class="form-control">
                <option value="">All roles</option>
                @foreach ($roles as $role)
                    <option value="{{ $role->id }}" @selected((string) request('role_id') === (string) $role->id)>{{ $role->name }}</option>
                @endforeach
                <option value="none" @selected(request('role_id') === 'none')>No role</option>
            </select>
        </div>
        <div>
            <label class="form-label">Branch</label>
            <select name="branch_id" class="form-control">
                <option value="">All branches</option>
                @foreach ($branches as $branch)
                    <option value="{{ $branch->id }}" @selected((string) request('branch_id') === (string) $branch->id)>{{ $branch->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Status</label>
            <select name="status" class="form-control">
                <option value="">All</option>
                <option value="active" @selected(request('status') === 'active')>Active</option>
                <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
            </select>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="btn btn-primary"><i class="ri-search-line"></i> Filter</button>
            <a href="{{ route('admin.employees.index') }}" class="btn btn-secondary">Reset</a>
        </div>
    </form>
</div>

<div class="card">
    <div class="table-wrap rounded-t-xl border-0">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Job Title</th>
                    <th>Role</th>
                    <th>Branch</th>
                    <th>Access</th>
                    <th>Status</th>
                    <th class="text-right">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($employees as $employee)
                    @php
                        $effective = $employee->effectivePermissions();
                        $rolePerms = $employee->roleModel?->permissions ?? [];
                        $extra = array_values(array_diff($effective, (array) $rolePerms));
                    @endphp
                    <tr class="{{ ($employee->status ?? 'active') !== 'active' ? 'opacity-60' : '' }}">
                        <td>
                            <div class="font-medium text-slate-800">{{ $employee->name }}</div>
                            <div class="text-xs text-slate-500">{{ $employee->email }}</div>
                            @if ($employee->phone)
                                <div class="text-xs text-slate-500">{{ $employee->phone }}</div>
                            @endif
                        </td>
                        <td>
                            @if ($employee->job_title)
                                <span class="badge badge-light">{{ $employee->job_title }}</span>
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </td>
                        <td>
                            @if ($employee->roleModel)
                                <span class="badge badge-light">{{ $employee->roleModel->name }}</span>
                            @else
                                <span class="text-slate-400 text-xs">No role</span>
                            @endif
                        </td>
                        <td class="text-slate-500 text-sm">{{ $employee->branch?->name ?? '—' }}</td>
                        <td class="text-xs text-slate-500 max-w-xs">
                            <span class="badge {{ count($effective) ? 'badge-success' : 'badge-light' }}">{{ count($effective) }} permissions</span>
                            @if (count($effective))
                                <div class="mt-1 truncate" title="{{ implode(', ', $effective) }}">{{ implode(', ', $effective) }}</div>
                            @endif
                            @if (count($extra))
                                <div class="text-[11px] text-amber-600">+{{ count($extra) }} extra direct</div>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ ($employee->status ?? 'active') === 'active' ? 'badge-success' : 'badge-light' }}">
                                {{ ucfirst($employee->status ?? 'active') }}
                            </span>
                            <div class="text-[11px] text-slate-400 mt-1">{{ $employee->created_at?->format('d M Y') }}</div>
                        </td>
                        <td class="text-right">
                            <div class="inline-flex items-center gap-1 justify-end">
                                <button class="btn btn-primary btn-sm" title="Edit" @click="$dispatch('open-modal', { id: 'editEmployee-{{ $employee->id }}' })">
                                    <i class="ri-edit-box-line"></i>
                                </button>
                                <form action="{{ route('admin.employees.toggleStatus', $employee) }}" method="post" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-secondary btn-sm" title="{{ ($employee->status ?? 'active') === 'active' ? 'Deactivate' : 'Activate' }}">
                                        <i class="{{ ($employee->status ?? 'active') === 'active' ? 'ri-pause-circle-line' : 'ri-play-circle-line' }}"></i>
                                    </button>
                                </form>
                                <form action="{{ route('admin.employees.destroy', $employee) }}" method="post" class="inline"
                                      onsubmit="return confirm('Delete {{ addslashes($employee->name) }}? They will lose login access.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" title="Delete"><i class="ri-delete-bin-line"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>

                    <div x-data="{ open: false }" @open-modal.window="if ($event.detail.id === 'editEmployee-{{ $employee->id }}') open = true" @keydown.escape.window="open = false">
                        <template x-teleport="body">
                            <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none">
                                <div class="modal-backdrop" @click="open = false"></div>
                                <div class="modal-dialog relative !max-w-2xl">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit Employee — {{ $employee->name }}</h5>
                                            <button type="button" class="text-slate-500 hover:text-slate-800" @click="open = false" aria-label="Close">
                                                <i class="ri-close-line text-xl"></i>
                                            </button>
                                        </div>
                                        <form action="{{ route('admin.employees.update', $employee->id) }}" method="post">
                                            @csrf
                                            <div class="modal-body space-y-4 max-h-[70vh] overflow-y-auto">
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                                    <div class="form-group">
                                                        <label class="form-label">Name *</label>
                                                        <input name="name" type="text" class="form-control" value="{{ $employee->name }}" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="form-label">Email (login) *</label>
                                                        <input name="email" type="email" class="form-control" value="{{ $employee->email }}" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="form-label">Phone</label>
                                                        <input name="phone" type="text" class="form-control" value="{{ $employee->phone }}">
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="form-label">New Password (optional)</label>
                                                        <input name="password" type="password" class="form-control" minlength="8" placeholder="Leave blank to keep">
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="form-label">Job Title</label>
                                                        <input name="job_title" type="text" class="form-control" list="jobTitles-{{ $employee->id }}" value="{{ $employee->job_title }}" placeholder="e.g. Waiter">
                                                        <datalist id="jobTitles-{{ $employee->id }}">
                                                            @foreach ($jobTitles as $jt)
                                                                <option value="{{ $jt }}"></option>
                                                            @endforeach
                                                        </datalist>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="form-label">Branch</label>
                                                        <select name="branch_id" class="form-control">
                                                            <option value="">No branch</option>
                                                            @foreach ($branches as $branch)
                                                                <option value="{{ $branch->id }}" @selected((int) $employee->branch_id === (int) $branch->id)>{{ $branch->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="form-label">Role (base permissions)</label>
                                                        <select name="role_id" class="form-control">
                                                            <option value="">No role — direct only</option>
                                                            @foreach ($roles as $role)
                                                                <option value="{{ $role->id }}" @selected((int) $employee->role_id === (int) $role->id)>{{ $role->name }} ({{ count($role->permissions ?? []) }})</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="form-label">Status *</label>
                                                        <select name="status" class="form-control">
                                                            <option value="active" @selected(($employee->status ?? 'active') === 'active')>Active</option>
                                                            <option value="inactive" @selected(($employee->status ?? 'active') === 'inactive')>Inactive</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="form-label">Extra direct permissions <span class="font-normal text-slate-400">(added on top of the role)</span></label>
                                                    @if (empty($groups))
                                                        @foreach ($permissions as $permission)
                                                            <label class="flex items-center gap-2 text-sm py-0.5">
                                                                <input type="checkbox" name="permissions[]" value="{{ $permission }}" @checked(in_array($permission, $employee->permissions ?? []))>
                                                                {{ $permission }}
                                                            </label>
                                                        @endforeach
                                                    @else
                                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                                            @foreach ($groups as $groupName => $groupPerms)
                                                                <div class="rounded-lg border border-slate-200 p-3">
                                                                    <div class="text-xs font-semibold uppercase tracking-wide text-slate-500 mb-2">{{ $groupName }}</div>
                                                                    @foreach ($groupPerms as $permKey => $permLabel)
                                                                        <label class="flex items-center gap-2 text-sm py-0.5">
                                                                            <input type="checkbox" name="permissions[]" value="{{ $permKey }}" @checked(in_array($permKey, $employee->permissions ?? []))>
                                                                            <span title="{{ $permKey }}">{{ $permLabel }}</span>
                                                                        </label>
                                                                    @endforeach
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @endif
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
                    <tr><td colspan="7">
                        <div class="empty-state">
                            <i class="ri-user-star-line"></i>
                            <h3>No employees found</h3>
                            <p>Try a different filter, or add an employee with login + role.</p>
                        </div>
                    </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($employees->hasPages())
        <div class="card-footer">
            {{ $employees->links() }}
        </div>
    @endif
</div>

<div x-data="{ open: false }" @open-modal.window="if ($event.detail.id === 'addEmployee') open = true" @keydown.escape.window="open = false">
    <template x-teleport="body">
        <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none">
            <div class="modal-backdrop" @click="open = false"></div>
            <div class="modal-dialog relative !max-w-2xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Employee</h5>
                        <button type="button" class="text-slate-500 hover:text-slate-800" @click="open = false" aria-label="Close">
                            <i class="ri-close-line text-xl"></i>
                        </button>
                    </div>
                    <form action="{{ route('admin.employees.store') }}" method="post">
                        @csrf
                        <div class="modal-body space-y-4 max-h-[70vh] overflow-y-auto">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div class="form-group">
                                    <label class="form-label">Name *</label>
                                    <input name="name" type="text" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Email (login) *</label>
                                    <input name="email" type="email" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Phone</label>
                                    <input name="phone" type="text" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Password *</label>
                                    <input name="password" type="password" class="form-control" minlength="8" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Job Title</label>
                                    <input name="job_title" type="text" class="form-control" list="jobTitles-new" placeholder="e.g. Cashier">
                                    <datalist id="jobTitles-new">
                                        @foreach ($jobTitles as $jt)
                                            <option value="{{ $jt }}"></option>
                                        @endforeach
                                    </datalist>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Branch</label>
                                    <select name="branch_id" class="form-control">
                                        <option value="">No branch</option>
                                        @foreach ($branches as $branch)
                                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Role (base permissions)</label>
                                    <select name="role_id" class="form-control">
                                        <option value="">No role — direct only</option>
                                        @foreach ($roles as $role)
                                            <option value="{{ $role->id }}">{{ $role->name }} ({{ count($role->permissions ?? []) }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Status *</label>
                                    <select name="status" class="form-control">
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Extra direct permissions <span class="font-normal text-slate-400">(added on top of the role)</span></label>
                                @if (empty($groups))
                                    @foreach ($permissions as $permission)
                                        <label class="flex items-center gap-2 text-sm py-0.5">
                                            <input type="checkbox" name="permissions[]" value="{{ $permission }}">
                                            {{ $permission }}
                                        </label>
                                    @endforeach
                                @else
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                        @foreach ($groups as $groupName => $groupPerms)
                                            <div class="rounded-lg border border-slate-200 p-3">
                                                <div class="text-xs font-semibold uppercase tracking-wide text-slate-500 mb-2">{{ $groupName }}</div>
                                                @foreach ($groupPerms as $permKey => $permLabel)
                                                    <label class="flex items-center gap-2 text-sm py-0.5">
                                                        <input type="checkbox" name="permissions[]" value="{{ $permKey }}">
                                                        <span title="{{ $permKey }}">{{ $permLabel }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" @click="open = false">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </template>
</div>

@endsection
