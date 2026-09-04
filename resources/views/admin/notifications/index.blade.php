@extends('layouts.admin')
@section('title', 'Notifications')
@section('page_title', 'Notifications')
@section('breadcrumb')
<a href="{{ route('admin.dashboard') }}">Home</a>
<span class="separator">/</span>
<span class="current">Notifications</span>
@endsection

@section('content')

<div class="page-header">
    <div>
        <p class="page-subtitle">{{ $notifications->count() }} recent alerts</p>
    </div>
</div>

<div class="card">
    <div class="divide-y divide-slate-100">
        @forelse ($notifications as $n)
            <div class="flex gap-3 px-4 py-3.5 {{ is_null($n->read_at) ? 'bg-orange-50/50' : '' }}">
                <span class="shrink-0 flex items-center justify-center h-10 w-10 rounded-full {{ \App\Models\StaffNotification::colorFor($n->type) }}">
                    <i class="{{ \App\Models\StaffNotification::iconFor($n->type) }} text-xl"></i>
                </span>
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-slate-900">{{ $n->title }}</p>
                    @if ($n->body)
                        <p class="text-sm text-slate-600 mt-0.5">{{ $n->body }}</p>
                    @endif
                    <p class="text-xs text-slate-400 mt-1">{{ $n->created_at?->diffForHumans() }} · {{ human_time($n->created_at) }}</p>
                </div>
            </div>
        @empty
            <div class="empty-state py-10">
                <i class="ri-notification-off-line"></i>
                <h3>No notifications yet</h3>
                <p>Storefront reservations and orders will appear here instantly.</p>
            </div>
        @endforelse
    </div>
</div>

@endsection
