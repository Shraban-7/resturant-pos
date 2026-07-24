@extends('layouts.admin')
@section('title', 'Employees')
@section('page_title', 'Employees')
@section('breadcrumb')
<a href="{{ route('seller.dashboard') }}">Home</a>
<span class="separator">/</span>
<span class="current">Employees</span>
@endsection

@section('content')

<div class="page-header">
    <div>
        <p class="page-subtitle">{{ $employees->total() }} {{ Str::plural('employee', $employees->total()) }} on staff</p>
    </div>
    <div class="page-actions">
        <button type="button" class="btn btn-primary" @click="$dispatch('open-modal', { id: 'addEmployee' })">
            <i class="ri-add-line"></i> Add Employee
        </button>
    </div>
</div>

<div class="card">
    <div class="table-wrap rounded-t-xl border-0">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Role</th>
                    <th>Branch</th>
                    <th>Employee Since</th>
                    <th class="text-right">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($employees as $employee)
                    <tr>
                        <td class="font-medium text-slate-800">{{ $employee->name }}</td>
                        <td><span class="badge badge-light">{{ ucfirst($employee->role) }}</span></td>
                        <td class="text-slate-500">{{ $employee->branch?->name ?? '—' }}</td>
                        <td class="text-slate-500">{{ $employee->created_at->format('d M Y') }}</td>
                        <td class="text-right">
                            <button class="btn btn-primary btn-sm" @click="$dispatch('open-modal', { id: 'editEmployee-{{ $employee->id }}' })">
                                <i class="ri-edit-box-line"></i> Edit
                            </button>
                        </td>
                    </tr>

                    <div x-data="{ open: false }" @open-modal.window="if ($event.detail.id === 'editEmployee-{{ $employee->id }}') open = true" @keydown.escape.window="open = false">
                        <template x-teleport="body">
                            <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none">
                                <div class="modal-backdrop" @click="open = false"></div>
                                <div class="modal-dialog modal-sm relative">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit Employee</h5>
                                            <button type="button" class="text-slate-500 hover:text-slate-800" @click="open = false" aria-label="Close">
                                                <i class="ri-close-line text-xl"></i>
                                            </button>
                                        </div>
                                        <form action="{{ route('seller.employees.update', $employee->id ) }}" method="post">
                                            @csrf
                                            <div class="modal-body">
                                                <div class="form-group">
                                                    <label class="form-label">Name</label>
                                                    <input name="name" type="text" class="form-control" value="{{ $employee->name }}" required>
                                                </div>
                                                <div class="form-group">
                                                    <label class="form-label">Role</label>
                                                    <select name="role" class="form-select form-control-sm">
                                                        @foreach ($roles as $role)
                                                            <option value="{{ $role }}" {{ $employee->role === $role ? 'selected' : '' }}>
                                                                {{ ucfirst($role) }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                @if(($branches ?? collect())->isNotEmpty())
                                                    <div class="form-group">
                                                        <label class="form-label">Branch</label>
                                                        <select name="branch_id" class="form-select form-control-sm">
                                                            <option value="">Unassigned</option>
                                                            @foreach ($branches as $branch)
                                                                <option value="{{ $branch->id }}" @selected((int) $employee->branch_id === (int) $branch->id)>
                                                                    {{ $branch->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                @endif
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
                    <tr><td colspan="4">
                        <div class="empty-state">
                            <i class="ri-user-star-line"></i>
                            <h3>No employees yet</h3>
                            <p>Add employees to manage roles and assignments.</p>
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
            <div class="modal-dialog modal-sm relative">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Employee</h5>
                        <button type="button" class="text-slate-500 hover:text-slate-800" @click="open = false" aria-label="Close">
                            <i class="ri-close-line text-xl"></i>
                        </button>
                    </div>
                    <form action="{{ route('seller.employees.store') }}" method="post">
                        @csrf
                        <div class="modal-body">
                            <div class="form-group">
                                <label class="form-label">Name</label>
                                <input name="name" type="text" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Role</label>
                                <select name="role" class="form-select form-control-sm" required>
                                    <option selected value="">Choose Role</option>
                                    @foreach ($roles as $role)
                                        <option value="{{ $role }}">{{ ucfirst($role) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @if(($branches ?? collect())->isNotEmpty())
                                <div class="form-group">
                                    <label class="form-label">Branch</label>
                                    <select name="branch_id" class="form-select form-control-sm">
                                        <option value="">{{ active_branch()?->name ?? 'Unassigned' }}</option>
                                        @foreach ($branches as $branch)
                                            <option value="{{ $branch->id }}" @selected((int) active_branch_id() === (int) $branch->id)>
                                                {{ $branch->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
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
