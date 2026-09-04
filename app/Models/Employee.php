<?php

namespace App\Models;

use App\Enums\EmployeeRole;
use App\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Employee extends Model
{
    use BelongsToBranch, HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'role' => EmployeeRole::class,
    ];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function scopeWaiter($query)
    {
        return $query->where('role', EmployeeRole::WAITER);
    }

    public function scopeSelf($query)
    {
        return $query->where('admin_id', panel_owner_id());
    }

    public static function roles(): array
    {
        return \App\Enums\EmployeeRole::values();
    }
}



