<?php

namespace App\Models;

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
    ];

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function employees()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /** Owner account id for scoping (admin owns data, employee inherits). */
    public function ownerId(): int
    {
        return $this->role === 'employee' && $this->parent_id
            ? (int) $this->parent_id
            : (int) $this->id;
    }

    public function isAdmin(): bool
    {
        // Legacy `seller` / `supplier` accounts are admins of the single panel.
        return in_array($this->role, ['admin', 'seller', 'supplier'], true);
    }

    public function isEmployee(): bool
    {
        return $this->role === 'employee';
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
        return $query->whereIn('role', ['admin', 'seller', 'supplier']);
    }

    /** Back-compat: old `seller` role is now `admin`. */
    public function scopeSeller($query)
    {
        return $query->whereIn('role', ['admin', 'seller']);
    }

    /** Back-compat: old `supplier` role data now owned by admin. */
    public function scopeSupplier($query)
    {
        return $query->whereIn('role', ['admin', 'supplier']);
    }
}
