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
        <p class="page-subtitle">{{ $employees->total() }} {{ Str::plural('employee', $employees->total()) }} with login access</p>
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
                    <th>Email</th>
                    <th>Permissions</th>
                    <th>Employee Since</th>
                    <th class="text-right">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($employees as $employee)
                    <tr>
                        <td class="font-medium text-slate-800">{{ $employee->name }}</td>
                        <td class="text-slate-500">{{ $employee->email }}</td>
                        <td class="text-slate-500">{{ implode(', ', $employee->permissions ?? []) ?: '—' }}</td>
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
                                        <form action="{{ route('admin.employees.update', $employee->id ) }}" method="post">
                                            @csrf
                                            <div class="modal-body">
                                                <div class="form-group">
                                                    <label class="form-label">Name</label>
                                                    <input name="name" type="text" class="form-control" value="{{ $employee->name }}" required>
                                                </div>
                                                <div class="form-group">
                                                    <label class="form-label">Email</label>
                                                    <input name="email" type="email" class="form-control" value="{{ $employee->email }}" required>
                                                </div>
                                                <div class="form-group">
                                                    <label class="form-label">New Password (optional)</label>
                                                    <input name="password" type="password" class="form-control" minlength="8">
                                                </div>
                                                <div class="form-group">
                                                    <label class="form-label">Permissions</label>
                                                    @foreach ($permissions as $permission)
                                                        <label class="flex items-center gap-2 text-sm">
                                                            <input type="checkbox" name="permissions[]" value="{{ $permission }}" @checked(in_array($permission, $employee->permissions ?? []))>
                                                            {{ $permission }}
                                                        </label>
                                                    @endforeach
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
                            <i class="ri-user-star-line"></i>
                            <h3>No employees yet</h3>
                            <p>Add employees with login + permissions.</p>
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
                    <form action="{{ route('admin.employees.store') }}" method="post">
                        @csrf
                        <div class="modal-body">
                            <div class="form-group">
                                <label class="form-label">Name</label>
                                <input name="name" type="text" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Email (login)</label>
                                <input name="email" type="email" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Password</label>
                                <input name="password" type="password" class="form-control" minlength="8" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Permissions</label>
                                @foreach ($permissions as $permission)
                                    <label class="flex items-center gap-2 text-sm">
                                        <input type="checkbox" name="permissions[]" value="{{ $permission }}">
                                        {{ $permission }}
                                    </label>
                                @endforeach
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

