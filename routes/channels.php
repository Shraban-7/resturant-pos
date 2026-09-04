<?php

use App\Models\DiningTable;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('admin.{ownerId}.kds', function ($user, int $ownerId) {
    return $user
        && in_array($user->role?->value ?? null, ['admin', 'seller', 'employee'], true)
        && (int) $user->ownerId() === $ownerId;
});

Broadcast::channel('admin.{ownerId}.pos', function ($user, int $ownerId) {
    return $user
        && in_array($user->role?->value ?? null, ['admin', 'seller', 'employee'], true)
        && (int) $user->ownerId() === $ownerId;
});

Broadcast::channel('admin.{ownerId}.reservations', function ($user, int $ownerId) {
    return $user
        && in_array($user->role?->value ?? null, ['admin', 'seller', 'employee'], true)
        && (int) $user->ownerId() === $ownerId;
});

Broadcast::channel('admin.{ownerId}.tables', function ($user, int $ownerId) {
    return $user
        && in_array($user->role?->value ?? null, ['admin', 'seller', 'employee'], true)
        && (int) $user->ownerId() === $ownerId;
});

Broadcast::channel('admin.{ownerId}.staff', function ($user, int $ownerId) {
    if (! $user || ! in_array($user->role?->value ?? null, ['admin', 'seller', 'employee'], true)) {
        return false;
    }

    if ((int) $user->ownerId() !== $ownerId) {
        return false;
    }

    return [
        'id' => $user->id,
        'name' => $user->name,
        'role' => $user->role,
    ];
});

Broadcast::channel('table.{token}', function ($user, string $token) {
    // Guests use the public Channel("table.{token}") — no auth callback needed.
    // Keep this for any private listeners (e.g. authenticated panel tools).
    $table = DiningTable::query()
        ->where('qr_code_token', $token)
        ->first();

    if (! $table) {
        return false;
    }

    if ($user && in_array($user->role?->value ?? null, ['admin', 'seller', 'employee'], true)) {
        return (int) $user->ownerId() === (int) $table->admin_id;
    }

    return (bool) $table;
});




