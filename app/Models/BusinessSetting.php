<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessSetting extends Model
{
    use HasFactory;
    
    protected $guarded = ['id'];

    protected $casts = [
        'receipt_show_signature' => 'boolean',
        'vat_enabled' => 'boolean',
        'global_discount_enabled' => 'boolean',
    ];
}
