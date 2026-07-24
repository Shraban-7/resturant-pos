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

Broadcast::channel('seller.{sellerId}.kds', function ($user, int $sellerId) {
    return $user
        && ($user->role ?? null) === 'seller'
        && (int) ($user->seller_id ?? $user->id) === $sellerId;
});

Broadcast::channel('seller.{sellerId}.pos', function ($user, int $sellerId) {
    return $user
        && ($user->role ?? null) === 'seller'
        && (int) ($user->seller_id ?? $user->id) === $sellerId;
});

Broadcast::channel('seller.{sellerId}.tables', function ($user, int $sellerId) {
    return $user
        && ($user->role ?? null) === 'seller'
        && (int) ($user->seller_id ?? $user->id) === $sellerId;
});

Broadcast::channel('seller.{sellerId}.staff', function ($user, int $sellerId) {
    if (! $user || ($user->role ?? null) !== 'seller') {
        return false;
    }

    if ((int) ($user->seller_id ?? $user->id) !== $sellerId) {
        return false;
    }

    return [
        'id' => $user->id,
        'name' => $user->name,
        'role' => $user->role,
    ];
});

Broadcast::channel('table.{token}', function ($user, string $token) {
    $table = DiningTable::query()
        ->where('qr_code_token', $token)
        ->first();

    if (! $table) {
        return false;
    }

    if (($user->role ?? null) === 'seller') {
        return (int) $user->id === (int) $table->seller_id;
    }

    return true;
});
