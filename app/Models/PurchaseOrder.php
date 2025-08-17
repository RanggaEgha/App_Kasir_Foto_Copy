<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    protected $fillable = [
        'supplier_id','invoice_no','tanggal','metode_bayar',
        'subtotal','discount','tax_percent','tax_amount','grand_total',
    ];

    protected $casts = [
        'tanggal'     => 'date',
        'subtotal'    => 'decimal:2',
        'discount'    => 'decimal:2',
        'tax_percent' => 'decimal:2',
        'tax_amount'  => 'decimal:2',
        'grand_total' => 'decimal:2',
    ];

    // RELATIONS
    public function items()      { return $this->hasMany(PurchaseItem::class, 'purchase_order_id'); }
    public function supplier()   { return $this->belongsTo(Supplier::class); }

    // Backward compatible: $po->total akan membaca grand_total
    public function getTotalAttribute()
    {
        return (float) ($this->grand_total ?? 0);
    }

    // Utility: hitung ulang total dari items + diskon + pajak
    public function recalcTotals(): void
    {
        $sub    = $this->items->sum('subtotal');
        $disc   = (float) ($this->discount ?? 0);
        $taxPct = (float) ($this->tax_percent ?? 0);

        $taxBase = max(0, $sub - $disc);
        $taxAmt  = round($taxBase * ($taxPct / 100), 2);

        $this->subtotal    = $sub;
        $this->tax_amount  = $taxAmt;
        $this->grand_total = $taxBase + $taxAmt;
    }
}
