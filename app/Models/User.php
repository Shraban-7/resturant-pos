<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'role',
        'parent_id',
        'role_id',
        'status',
        'job_title',
        'branch_id',
        'name',
        'email',
        'phone',
        'password',
        'permissions',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'permissions' => 'array',
        'role' => UserRole::class,
    ];

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function employees()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function roleModel()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    /**
     * Canonical owner id for scoping. Single restaurant = single dataset:
     * every admin shares the first admin's data, employees inherit through
     * their parent chain.
     */
    public function ownerId(): int
    {
        if ($this->role === UserRole::EMPLOYEE && $this->parent_id) {
            $parent = static::query()->find($this->parent_id);

            return $parent ? $parent->ownerId() : (int) $this->parent_id;
        }

        if ($this->isAdmin()) {
            $first = static::query()
                ->whereIn('role', [UserRole::ADMIN, UserRole::SELLER, UserRole::SUPPLIER])
                ->orderBy('id')
                ->value('id');

            return $first ? (int) $first : (int) $this->id;
        }

        return (int) $this->id;
    }

    public function isAdmin(): bool
    {
        // Legacy seller/supplier accounts are admins of the single panel.
        return in_array($this->role, [UserRole::ADMIN, UserRole::SELLER, UserRole::SUPPLIER], true);
    }

    public function isEmployee(): bool
    {
        return $this->role === UserRole::EMPLOYEE;
    }

    public function isActive(): bool
    {
        return ($this->status ?? 'active') === 'active';
    }

    /**
     * Effective permissions = assigned Role's permissions + any extra
     * direct permissions stored on the user. Direct permissions can only
     * grant, never revoke, role permissions.
     */
    public function effectivePermissions(): array
    {
        $fromRole = [];
        try {
            $role = $this->relationLoaded('roleModel')
                ? $this->roleModel
                : $this->roleModel()->first();
            $fromRole = $role?->permissions ?? [];
        } catch (\Throwable $e) {
            $fromRole = [];
        }

        // Back-compat: role may be missing (fresh column) — fall back to direct.
        $direct = $this->permissions ?? [];

        return array_values(array_unique(array_merge((array) $fromRole, (array) $direct)));
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        if (! $this->isActive()) {
            return false;
        }

        return in_array($permission, $this->effectivePermissions(), true);
    }

    public function scopeAdmin($query)
    {
        // Legacy roles from before the single-panel RBAC still map to admin.
        return $query->whereIn('role', [UserRole::ADMIN, UserRole::SELLER, UserRole::SUPPLIER]);
    }

    public function scopeEmployees($query)
    {
        return $query->where('role', UserRole::EMPLOYEE);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
