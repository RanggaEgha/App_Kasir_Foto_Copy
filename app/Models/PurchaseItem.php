<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\HasAuditLogs;

class PurchaseItem extends Model
{
    use HasAuditLogs;
    
    protected $fillable = [
        'purchase_order_id','barang_id','unit_id','qty','unit_price','subtotal'
    ];

    protected $casts = [
        'qty'        => 'integer',
        'unit_price' => 'decimal:2',
        'subtotal'   => 'decimal:2',
    ];

    public function order() {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }
    public function barang() {
        return $this->belongsTo(Barang::class);
    }
    public function unit() {
        return $this->belongsTo(Unit::class);
    }

    protected static function booted()
    {
        static::saving(function (self $item) {
            $item->subtotal = (int)$item->qty * (float)$item->unit_price;
        });
    }
}
