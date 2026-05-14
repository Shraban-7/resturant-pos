@extends('layouts.admin')
@section('title', 'Dining Tables')
@section('page_title', 'Dining Tables')
@section('breadcrumb')
<a href="{{ route('seller.dashboard') }}">Home</a>
<span class="separator">/</span>
<span class="current">Tables</span>
@endsection

@section('content')

<div class="page-header">
    <div>
        <p class="page-subtitle">{{ $tables->count() }} {{ Str::plural('table', $tables->count()) }} configured</p>
    </div>
    <div class="page-actions">
        <button type="button" class="btn btn-primary" @click="$dispatch('open-modal', { id: 'addTable' })">
            <i class="ri-add-line"></i> Add New Table
        </button>
    </div>
</div>

<div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
    @forelse($tables as $table)
        @php
            $badgeClass = match ($table->status) {
                \App\Models\DiningTable::FREE => 'badge-success',
                \App\Models\DiningTable::RESERVED => 'badge-warning',
                default => 'badge-danger',
            };
        @endphp
        <div class="card">
            <div class="card-body text-center py-6">
                <div class="flex items-center justify-center mb-2 gap-1">
                    <h4 class="text-lg font-semibold text-slate-800">{{ $table->name }}</h4>
                    <button type="button" class="btn btn-sm btn-ghost p-1"
                            @click="$dispatch('open-modal', { id: 'editTable', table: { id: {{ $table->id }}, name: @js($table->name), status: @js($table->status) } })"
                            title="Edit">
                        <i class="ri-edit-box-line text-base"></i>
                    </button>
                </div>
                <span class="{{ $badgeClass }}">{{ ucfirst($table->status) }}</span>
            </div>
        </div>
    @empty
        <div class="col-span-full">
            <div class="card">
                <div class="empty-state">
                    <i class="ri-reserved-line"></i>
                    <h3>No tables configured</h3>
                    <p>Add dining tables to start tracking their status.</p>
                </div>
            </div>
        </div>
    @endforelse
</div>

<div x-data="{ open: false }"
     @open-modal.window="if ($event.detail && $event.detail.id === 'addTable') open = true"
     @keydown.escape.window="open = false">
    <template x-teleport="body">
        <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none">
            <div class="modal-backdrop" @click="open = false"></div>
            <div class="modal-dialog modal-sm relative">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Table</h5>
                        <button type="button" class="text-slate-500 hover:text-slate-800" @click="open = false" aria-label="Close">
                            <i class="ri-close-line text-xl"></i>
                        </button>
                    </div>
                    <form action="{{ route('seller.diningTables.store') }}" method="post">
                        @csrf
                        <div class="modal-body">
                            <div class="form-group">
                                <label class="form-label">Name</label>
                                <input name="name" type="text" class="form-control" required>
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

<div x-data="{ open: false, t: null, statuses: @js($tableStatus) }"
     @open-modal.window="const d = $event.detail; if (d && d.id === 'editTable' && d.table) { t = d.table; open = true; }"
     @keydown.escape.window="open = false">
    <template x-teleport="body">
        <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none">
            <div class="modal-backdrop" @click="open = false"></div>
            <div class="modal-dialog modal-sm relative">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Table</h5>
                        <button type="button" class="text-slate-500 hover:text-slate-800" @click="open = false" aria-label="Close">
                            <i class="ri-close-line text-xl"></i>
                        </button>
                    </div>
                    <form :action="t ? `/seller/dining-tables/${t.id}/update` : '#'" method="post">
                        @csrf
                        <div class="modal-body">
                            <div class="form-group">
                                <label class="form-label">Name</label>
                                <input name="name" type="text" class="form-control" :value="t ? t.name : ''" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select form-control-sm">
                                    <template x-for="status in statuses" :key="status">
                                        <option :value="status" :selected="t && t.status === status" x-text="status.charAt(0).toUpperCase() + status.slice(1)"></option>
                                    </template>
                                </select>
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

@endsection
