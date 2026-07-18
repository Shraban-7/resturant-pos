<?php

namespace App\Traits;

use Carbon\Carbon;

trait HasCommonScopes {

    public function scopeActive($query)
    {            
        return $query->where('is_active', 1);
    }

    public function scopeFilterByDate($query, $fromDate = null, $toDate = null)
    {
        $today = Carbon::today();
        $fromDate = is_null($fromDate) ? ''  : $fromDate;
        $toDate = is_null($toDate) ? '' : $toDate;
        $column = 'created_at';

        if ($fromDate != '') {
            $query->whereDate($column, '>=', $fromDate);
        }
        if ($toDate != '') {
            $query->whereDate($column, '<=', $toDate);
        }

        if ($fromDate == '' && $toDate == '') {
            $query->whereDate($column, $today);
        }

        return $query;
    }
}