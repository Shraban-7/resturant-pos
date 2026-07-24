<?php

namespace App\Traits;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToBranch
{
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function scopeForActiveBranch(Builder $query): Builder
    {
        $branchId = active_branch_id();

        if ($branchId) {
            return $query->where($query->getModel()->getTable().'.branch_id', $branchId);
        }

        return $query;
    }
}
