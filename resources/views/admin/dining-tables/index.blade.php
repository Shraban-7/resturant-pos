@extends('layouts.admin')
@section('title', 'Dining Tables')
@section('page_title', 'Dining Tables')
@section('breadcrumb')
<a href="{{ route('admin.dashboard') }}">Home</a>
<span class="separator">/</span>
<span class="current">Tables</span>
@endsection

@section('content')

<div class="page-header">
    <div>
        <p class="page-subtitle">{{ $tables->count() }} {{ Str::plural('table', $tables->count()) }} configured</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.diningTables.floorMap') }}" class="btn btn-secondary">
            <i class="ri-layout-masonry-line"></i> Floor Map
        </a>
        <button type="button" class="btn btn-primary" @click="$dispatch('open-modal', { id: 'addTable' })">
            <i class="ri-add-line"></i> Add New Table
        </button>
    </div>
</div>

<div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
    @forelse($tables as $table)
        @php
            $badgeClass = match ($table->status) {
                \App\Enums\TableStatus::FREE => 'badge-success',
                \App\Enums\TableStatus::RESERVED => 'badge-warning',
                \App\Enums\TableStatus::CLEANING => 'badge-primary',
                default => 'badge-danger',
            };
        @endphp
        <div class="card">
            <div class="card-body text-center py-6">
                <div class="flex items-center justify-center mb-2 gap-1">
                    <h4 class="text-lg font-semibold text-slate-800">{{ $table->name }}</h4>
                    <button type="button" class="btn btn-sm btn-ghost p-1"
                            @click="$dispatch('open-modal', { id: 'editTable', table: {
                                id: {{ $table->id }},
                                name: @js($table->name),
                                status: @js($table->status),
                                floor_id: {{ $table->floor_id ?: 'null' }}
                            } })"
                            title="Edit">
                        <i class="ri-edit-box-line text-base"></i>
                    </button>
                </div>
                <span class="{{ $badgeClass }}">{{ $table->status->label() }}</span>
                @if($table->floor)
                    <p class="text-xs text-slate-400 mt-1">{{ $table->floor->name }}</p>
                @endif
                @if($table->branch)
                    <p class="text-[10px] text-slate-400">{{ $table->branch->name }}</p>
                @endif
                <div class="mt-3 flex flex-col gap-2">
                    <a href="{{ route('admin.diningTables.qrCard', $table) }}" class="btn btn-secondary btn-sm w-full" target="_blank">
                        <i class="ri-qr-code-line"></i> QR Card
                    </a>
                    <a href="{{ route('menu.index', $table) }}" class="btn btn-ghost btn-sm w-full" target="_blank">
                        <i class="ri-external-link-line"></i> Open Menu
                    </a>
                </div>
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

@php $floorsJson = $floors->map(fn ($f) => ['id' => $f->id, 'name' => $f->name]); @endphp

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
                    <form action="{{ route('admin.diningTables.store') }}" method="post">
                        @csrf
                        <div class="modal-body">
                            <div class="form-group">
                                <label class="form-label">Name</label>
                                <input name="name" type="text" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Floor</label>
                                <select name="floor_id" class="form-select form-control">
                                    <option value="">Unassigned</option>
                                    @foreach($floors as $floor)
                                        <option value="{{ $floor->id }}">{{ $floor->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @if(($branches ?? collect())->isNotEmpty())
                                <div class="form-group">
                                    <label class="form-label">Branch</label>
                                    <select name="branch_id" class="form-select form-control">
                                        <option value="">{{ active_branch()?->name ?? 'Unassigned' }}</option>
                                        @foreach($branches as $branch)
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

<div x-data="{ open: false, t: null, statuses: @js($tableStatus), floors: @js($floorsJson) }"
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
                    <form :action="t ? `/admin/dining-tables/${t.id}/update` : '#'" method="post">
                        @csrf
                        <div class="modal-body">
                            <div class="form-group">
                                <label class="form-label">Name</label>
                                <input name="name" type="text" class="form-control" :value="t ? t.name : ''" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Floor</label>
                                <select name="floor_id" class="form-select form-control">
                                    <option value="">Unassigned</option>
                                    <template x-for="f in floors" :key="f.id">
                                        <option :value="f.id" :selected="t && t.floor_id == f.id" x-text="f.name"></option>
                                    </template>
                                </select>
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



