@extends('layouts.admin')
@section('title', 'Reservations')
@section('page_title', 'Reservations')
@section('breadcrumb')
<a href="{{ route('admin.dashboard') }}">Home</a>
<span class="separator">/</span>
<span class="current">Reservations</span>
@endsection

@section('content')

<div class="page-header">
    <div>
        <p class="page-subtitle">{{ $reservations->total() }} {{ Str::plural('reservation', $reservations->total()) }}</p>
    </div>
    <form class="page-actions flex flex-wrap gap-2 items-end" method="GET">
        @if ($branches->isNotEmpty())
            <div>
                <label class="form-label text-xs">Branch</label>
                <select name="branch_id" class="form-control form-control-sm" onchange="this.form.submit()">
                    <option value="">All branches</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" @selected((string) ($branchFilter ?? '') === (string) $branch->id)>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                    <option value="unassigned" @selected(($branchFilter ?? '') === 'unassigned')>Unassigned</option>
                </select>
            </div>
        @endif
        <button type="button" class="btn btn-primary" @click="$dispatch('open-modal', { id: 'addReservation' })">
            <i class="ri-add-line"></i> New Reservation
        </button>
    </form>
</div>

<div class="card">
    <div class="table-wrap rounded-t-xl border-0">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Phone</th>
                    <th>Table</th>
                    <th>Guests</th>
                    <th>Time</th>
                    <th>Status</th>
                    <th class="text-right">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($reservations as $reservation)
                    <tr>
                        <td class="font-medium text-slate-800">{{ $reservation->customer_name }}</td>
                        <td class="text-slate-500">{{ $reservation->customer_phone }}</td>
                        <td>{{ $reservation->table?->name ?? '—' }}</td>
                        <td>{{ $reservation->guest_count }}</td>
                        <td>{{ human_time($reservation->reservation_time) }}</td>
                        <td>
                            @php
                                $badge = match ($reservation->status) {
                                    \App\Enums\ReservationStatus::CONFIRMED => 'badge-success',
                                    \App\Enums\ReservationStatus::PENDING => 'badge-warning',
                                    \App\Enums\ReservationStatus::SEATED => 'badge-primary',
                                    default => 'badge-light',
                                };
                            @endphp
                            <span class="{{ $badge }}">{{ $reservation->status->label() }}</span>
                        </td>
                        <td class="text-right space-x-1">
                            <button class="btn btn-primary btn-sm"
                                    @click="$dispatch('open-modal', { id: 'editReservation', reservation: {
                                        id: {{ $reservation->id }},
                                        table_id: {{ $reservation->table_id }},
                                        customer_name: @js($reservation->customer_name),
                                        customer_phone: @js($reservation->customer_phone),
                                        guest_count: {{ $reservation->guest_count }},
                                        reservation_time: @js(optional($reservation->reservation_time)->format('Y-m-d\TH:i')),
                                        status: @js($reservation->status->value),
                                        notes: @js($reservation->notes)
                                    } })">
                                <i class="ri-edit-box-line"></i>
                            </button>
                            <form action="{{ route('admin.reservations.destroy', $reservation) }}" method="post" class="inline"
                                  onsubmit="return confirm('Delete this reservation?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm"><i class="ri-delete-bin-line"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <div class="empty-state py-10">
                                <i class="ri-calendar-check-line"></i>
                                <h3>No reservations</h3>
                                <p>Book a table for upcoming guests.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($reservations->hasPages())
        <div class="card-footer">{{ $reservations->links() }}</div>
    @endif
</div>

@php $tablesJson = $tables->map(fn ($t) => ['id' => $t->id, 'name' => $t->name]); @endphp

<div x-data="{ open: false }"
     @open-modal.window="if ($event.detail && $event.detail.id === 'addReservation') open = true"
     @keydown.escape.window="open = false">
    <template x-teleport="body">
        <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none">
            <div class="modal-backdrop" @click="open = false"></div>
            <div class="modal-dialog relative">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">New Reservation</h5>
                        <button type="button" class="text-slate-500 hover:text-slate-800" @click="open = false"><i class="ri-close-line text-xl"></i></button>
                    </div>
                    <form action="{{ route('admin.reservations.store') }}" method="post">
                        @csrf
                        <div class="modal-body grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div class="form-group sm:col-span-2">
                                <label class="form-label">Table</label>
                                <select name="table_id" class="form-select form-control" required>
                                    <option value="">Select table</option>
                                    @foreach($tables as $table)
                                        <option value="{{ $table->id }}">{{ $table->name }} ({{ $table->status }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Customer name</label>
                                <input name="customer_name" type="text" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Phone</label>
                                <input name="customer_phone" type="text" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Guests</label>
                                <input name="guest_count" type="number" class="form-control" value="2" min="1">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Date & time</label>
                                <input name="reservation_time" type="datetime-local" class="form-control" required>
                            </div>
                            <div class="form-group sm:col-span-2">
                                <label class="form-label">Notes</label>
                                <textarea name="notes" class="form-control" rows="2"></textarea>
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

<div x-data="{ open: false, r: null, tables: @js($tablesJson), statuses: @js($statuses) }"
     @open-modal.window="const d = $event.detail; if (d && d.id === 'editReservation' && d.reservation) { r = d.reservation; open = true; }"
     @keydown.escape.window="open = false">
    <template x-teleport="body">
        <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none">
            <div class="modal-backdrop" @click="open = false"></div>
            <div class="modal-dialog relative">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Reservation</h5>
                        <button type="button" class="text-slate-500 hover:text-slate-800" @click="open = false"><i class="ri-close-line text-xl"></i></button>
                    </div>
                    <form :action="r ? `/admin/reservations/${r.id}` : '#'" method="post">
                        @csrf
                        @method('PUT')
                        <div class="modal-body grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div class="form-group sm:col-span-2">
                                <label class="form-label">Table</label>
                                <select name="table_id" class="form-select form-control" required>
                                    <template x-for="t in tables" :key="t.id">
                                        <option :value="t.id" :selected="r && r.table_id == t.id" x-text="t.name"></option>
                                    </template>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Customer name</label>
                                <input name="customer_name" type="text" class="form-control" :value="r ? r.customer_name : ''" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Phone</label>
                                <input name="customer_phone" type="text" class="form-control" :value="r ? r.customer_phone : ''" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Guests</label>
                                <input name="guest_count" type="number" class="form-control" :value="r ? r.guest_count : 1" min="1">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Date & time</label>
                                <input name="reservation_time" type="datetime-local" class="form-control" :value="r ? r.reservation_time : ''" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select form-control" required>
                                    <template x-for="s in statuses" :key="s">
                                        <option :value="s" :selected="r && r.status === s" x-text="s.charAt(0).toUpperCase() + s.slice(1)"></option>
                                    </template>
                                </select>
                            </div>
                            <div class="form-group sm:col-span-2">
                                <label class="form-label">Notes</label>
                                <textarea name="notes" class="form-control" rows="2" :value="r ? (r.notes || '') : ''"></textarea>
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

