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

    /**
     * Canonical owner id for scoping. Single restaurant = single dataset:
     * every admin shares the first admin's data, employees inherit through
     * their parent chain. This keeps the public storefront (which books
     * under the first admin) visible to whichever admin is logged in.
     */
    public function ownerId(): int
    {
        if ($this->role === UserRole::EMPLOYEE && $this->parent_id) {
            $parent = static::query()->find($this->parent_id);

            return $parent ? $parent->ownerId() : (int) $this->parent_id;
        }

        if ($this->isAdmin()) {
            $first = static::query()
                ->whereIn('role', [UserRole::ADMIN, 'seller', 'supplier'])
                ->orderBy('id')
                ->value('id');

            return $first ? (int) $first : (int) $this->id;
        }

        return (int) $this->id;
    }

    /** First admin = canonical store owner (used by the public storefront). */
    public static function storeOwner(): ?self
    {
        return static::admin()->orderBy('id')->first();
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

    public function hasPermission(string $permission): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        return in_array($permission, $this->permissions ?? [], true);
    }

    public function scopeAdmin($query)
    {
        // Legacy roles from before the single-panel RBAC still map to admin.
        return $query->whereIn('role', [UserRole::ADMIN, 'seller', 'supplier']);
    }
}
