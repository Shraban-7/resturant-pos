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

Broadcast::channel('admin.{sellerId}.kds', function ($user, int $sellerId) {
    return $user
        && in_array($user->role ?? null, ['admin', 'seller', 'employee'], true)
        && (int) $user->ownerId() === $sellerId;
});

Broadcast::channel('admin.{sellerId}.pos', function ($user, int $sellerId) {
    return $user
        && in_array($user->role ?? null, ['admin', 'seller', 'employee'], true)
        && (int) $user->ownerId() === $sellerId;
});

Broadcast::channel('admin.{sellerId}.reservations', function ($user, int $sellerId) {
    return $user
        && in_array($user->role ?? null, ['admin', 'seller', 'employee'], true)
        && (int) $user->ownerId() === $sellerId;
});

Broadcast::channel('admin.{sellerId}.tables', function ($user, int $sellerId) {
    return $user
        && in_array($user->role ?? null, ['admin', 'seller', 'employee'], true)
        && (int) $user->ownerId() === $sellerId;
});

Broadcast::channel('admin.{sellerId}.staff', function ($user, int $sellerId) {
    if (! $user || ! in_array($user->role ?? null, ['admin', 'seller', 'employee'], true)) {
        return false;
    }

    if ((int) $user->ownerId() !== $sellerId) {
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

    if ($user && in_array($user->role ?? null, ['admin', 'seller', 'employee'], true)) {
        return (int) $user->ownerId() === (int) $table->seller_id;
    }

    return (bool) $table;
});

