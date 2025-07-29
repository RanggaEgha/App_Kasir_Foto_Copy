<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseItem extends Model
{
    protected $fillable = [
        'purchase_id','barang_id','quantity','unit_price','subtotal'
    ];

    public function barang()   { return $this->belongsTo(Barang::class); }
    public function purchase() { return $this->belongsTo(PurchaseOrder::class, 'purchase_id'); }
}
