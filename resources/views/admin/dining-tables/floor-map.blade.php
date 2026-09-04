@extends('layouts.admin')
@section('title', 'Floor Map')
@section('page_title', 'Floor Map')
@section('breadcrumb')
<a href="{{ route('admin.dashboard') }}">Home</a>
<span class="separator">/</span>
<a href="{{ route('admin.floors.index') }}">Floors</a>
<span class="separator">/</span>
<span class="current">Map</span>
@endsection

@section('content')

<div class="page-header">
    <div>
        <p class="page-subtitle">Drag tables to arrange the floor plan, then save.</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.floors.index') }}" class="btn btn-secondary"><i class="ri-building-line"></i> Floors</a>
        <a href="{{ route('admin.diningTables.index') }}" class="btn btn-secondary"><i class="ri-reserved-line"></i> Tables</a>
        <button type="button" id="saveFloorPlanBtn" class="btn btn-primary">
            <i class="ri-save-line"></i> Save Layout
        </button>
    </div>
</div>

<div class="flex flex-wrap gap-2 mb-4">
    @forelse($floors as $floor)
        <a href="{{ route('admin.diningTables.floorMap', ['floor_id' => $floor->id]) }}"
           class="btn btn-sm {{ (int) $floorId === (int) $floor->id ? 'btn-primary' : 'btn-secondary' }}">
            {{ $floor->name }}
        </a>
    @empty
        <p class="text-slate-500 text-sm">Create a floor first, then assign tables to it.</p>
    @endforelse
    <a href="{{ route('admin.diningTables.floorMap', ['floor_id' => 0]) }}"
       class="btn btn-sm {{ $floorId === null ? 'btn-primary' : 'btn-secondary' }}">Unassigned</a>
</div>

<div class="card overflow-hidden">
    <div id="floorCanvas"
         class="relative bg-slate-100 border-b border-slate-200"
         style="height: 520px; background-image: linear-gradient(#e2e8f0 1px, transparent 1px), linear-gradient(90deg, #e2e8f0 1px, transparent 1px); background-size: 40px 40px;">
        @forelse($tables as $table)
            @php
                $color = match ($table->status) {
                    \App\Enums\TableStatus::FREE => 'bg-emerald-500',
                    \App\Enums\TableStatus::RESERVED => 'bg-amber-500',
                    \App\Enums\TableStatus::CLEANING => 'bg-sky-500',
                    default => 'bg-rose-500',
                };
            @endphp
            <div class="floor-table absolute w-24 h-24 rounded-xl shadow-md text-white flex flex-col items-center justify-center cursor-grab select-none {{ $color }}"
                 data-id="{{ $table->id }}"
                 style="left: {{ (int) $table->x_position }}px; top: {{ (int) $table->y_position }}px;">
                <span class="font-semibold text-sm">{{ $table->name }}</span>
                <span class="text-[10px] uppercase opacity-90">{{ $table->status->value }}</span>
            </div>
        @empty
            <div class="absolute inset-0 flex items-center justify-center text-slate-400">
                No tables on this floor. Assign a floor when creating or editing a table.
            </div>
        @endforelse
    </div>
</div>

@endsection

@push('footer')
<script>
(function () {
    const canvas = document.getElementById('floorCanvas');
    if (!canvas) return;

    let active = null;
    let offsetX = 0;
    let offsetY = 0;

    canvas.querySelectorAll('.floor-table').forEach((el) => {
        el.addEventListener('pointerdown', (e) => {
            active = el;
            el.setPointerCapture(e.pointerId);
            const rect = el.getBoundingClientRect();
            offsetX = e.clientX - rect.left;
            offsetY = e.clientY - rect.top;
            el.classList.add('cursor-grabbing');
        });

        el.addEventListener('pointermove', (e) => {
            if (!active || active !== el) return;
            const canvasRect = canvas.getBoundingClientRect();
            let x = e.clientX - canvasRect.left - offsetX;
            let y = e.clientY - canvasRect.top - offsetY;
            x = Math.max(0, Math.min(x, canvas.clientWidth - el.offsetWidth));
            y = Math.max(0, Math.min(y, canvas.clientHeight - el.offsetHeight));
            el.style.left = Math.round(x) + 'px';
            el.style.top = Math.round(y) + 'px';
        });

        el.addEventListener('pointerup', () => {
            if (active) active.classList.remove('cursor-grabbing');
            active = null;
        });
    });

    document.getElementById('saveFloorPlanBtn')?.addEventListener('click', async () => {
        const positions = Array.from(canvas.querySelectorAll('.floor-table')).map((el) => ({
            id: parseInt(el.dataset.id, 10),
            x: parseInt(el.style.left, 10) || 0,
            y: parseInt(el.style.top, 10) || 0,
        }));

        const token = document.querySelector('meta[name="csrf-token"]')?.content;
        try {
            const res = await fetch(@json(route('admin.diningTables.savePositions')), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ positions }),
            });
            const data = await res.json();
            if (!res.ok || !data.status) throw new Error(data.message || 'Save failed');
            window.toast?.success(data.message || 'Saved');
        } catch (err) {
            window.toast?.error(err.message || 'Could not save floor plan');
        }
    });
})();
</script>
@endpush



