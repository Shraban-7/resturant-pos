<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Role extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'permissions' => 'array',
        'is_system' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (Role $role) {
            if (empty($role->slug) && ! empty($role->name)) {
                $role->slug = Str::slug($role->name);
            }
        });
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'role_id');
    }

    public function scopeSelf($query)
    {
        return $query->where('admin_id', panel_owner_id());
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /** Default role templates used for seeding + "reset defaults". */
    public static function defaultTemplates(): array
    {
        return config('rbac.default_roles', [
            'Manager' => ['dashboard', 'pos', 'products', 'stocks', 'sales', 'kds', 'floors', 'branches', 'reservations', 'gift-cards', 'employees', 'reports', 'settings'],
            'Cashier' => ['dashboard', 'pos', 'sales', 'gift-cards', 'reservations'],
            'Waiter' => ['pos', 'kds', 'floors', 'reservations', 'sales'],
            'Chef' => ['kds', 'dashboard'],
        ]);
    }
}
