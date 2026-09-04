@extends('layouts.admin')
@section('title', 'Floors')
@section('page_title', 'Floor Zones')
@section('breadcrumb')
<a href="{{ route('admin.dashboard') }}">Home</a>
<span class="separator">/</span>
<span class="current">Floors</span>
@endsection

@section('content')

<div class="page-header">
    <div>
        <p class="page-subtitle">{{ $floors->count() }} {{ Str::plural('floor', $floors->count()) }} configured</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.diningTables.floorMap') }}" class="btn btn-secondary">
            <i class="ri-layout-masonry-line"></i> Floor Map
        </a>
        <button type="button" class="btn btn-primary" @click="$dispatch('open-modal', { id: 'addFloor' })">
            <i class="ri-add-line"></i> Add Floor
        </button>
    </div>
</div>

<div class="card">
    <div class="table-wrap rounded-t-xl border-0">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Priority</th>
                    <th>Tables</th>
                    <th class="text-right">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($floors as $floor)
                    <tr>
                        <td class="font-medium text-slate-800">{{ $floor->name }}</td>
                        <td>{{ $floor->priority }}</td>
                        <td><span class="badge badge-light">{{ $floor->tables_count }}</span></td>
                        <td class="text-right space-x-1">
                            <a href="{{ route('admin.diningTables.floorMap', ['floor_id' => $floor->id]) }}" class="btn btn-secondary btn-sm">
                                <i class="ri-layout-masonry-line"></i>
                            </a>
                            <button class="btn btn-primary btn-sm"
                                    @click="$dispatch('open-modal', { id: 'editFloor', floor: { id: {{ $floor->id }}, name: @js($floor->name), priority: {{ $floor->priority }} } })">
                                <i class="ri-edit-box-line"></i> Edit
                            </button>
                            <form action="{{ route('admin.floors.destroy', $floor) }}" method="post" class="inline"
                                  onsubmit="return confirm('Delete this floor? Tables will be unassigned.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm"><i class="ri-delete-bin-line"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">
                            <div class="empty-state py-10">
                                <i class="ri-building-line"></i>
                                <h3>No floors yet</h3>
                                <p>Create zones like Main Room or Patio, then place tables on the floor map.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div x-data="{ open: false }"
     @open-modal.window="if ($event.detail && $event.detail.id === 'addFloor') open = true"
     @keydown.escape.window="open = false">
    <template x-teleport="body">
        <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none">
            <div class="modal-backdrop" @click="open = false"></div>
            <div class="modal-dialog modal-sm relative">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Floor</h5>
                        <button type="button" class="text-slate-500 hover:text-slate-800" @click="open = false"><i class="ri-close-line text-xl"></i></button>
                    </div>
                    <form action="{{ route('admin.floors.store') }}" method="post">
                        @csrf
                        <div class="modal-body">
                            <div class="form-group">
                                <label class="form-label">Name</label>
                                <input name="name" type="text" class="form-control" required placeholder="Main Room">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Priority</label>
                                <input name="priority" type="number" class="form-control" value="0" min="0">
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

<div x-data="{ open: false, f: null }"
     @open-modal.window="const d = $event.detail; if (d && d.id === 'editFloor' && d.floor) { f = d.floor; open = true; }"
     @keydown.escape.window="open = false">
    <template x-teleport="body">
        <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none">
            <div class="modal-backdrop" @click="open = false"></div>
            <div class="modal-dialog modal-sm relative">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Floor</h5>
                        <button type="button" class="text-slate-500 hover:text-slate-800" @click="open = false"><i class="ri-close-line text-xl"></i></button>
                    </div>
                    <form :action="f ? `/admin/floors/${f.id}` : '#'" method="post">
                        @csrf
                        @method('PUT')
                        <div class="modal-body">
                            <div class="form-group">
                                <label class="form-label">Name</label>
                                <input name="name" type="text" class="form-control" :value="f ? f.name : ''" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Priority</label>
                                <input name="priority" type="number" class="form-control" :value="f ? f.priority : 0" min="0">
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

