<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiscountRule extends Model
{
    protected $fillable = [
        'target_type',      // barang | jasa
        'target_id',        // nullable → berlaku untuk semua target_type
        'min_qty',
        'discount_type',    // percent | amount
        'discount_value',
        'is_active',
    ];

    protected $casts = [
        'min_qty'       => 'integer',
        'discount_value'=> 'integer',
        'is_active'     => 'boolean',
    ];
}

