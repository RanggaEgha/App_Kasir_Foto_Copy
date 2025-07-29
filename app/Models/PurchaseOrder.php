<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    protected $fillable = [
        'supplier_id','invoice_no','purchase_date',
        'subtotal','discount','tax','total',
        'payment_method','amount_paid','change_amount',
        'status','notes'
    ];
    protected $casts = [
        'purchase_date' => 'datetime',
    ];

    public function supplier() { return $this->belongsTo(Supplier::class); }
    public function items()    { return $this->hasMany(PurchaseItem::class, 'purchase_id'); }
}
