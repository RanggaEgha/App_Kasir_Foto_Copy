<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\HasAuditLogs;

class TransaksiItem extends Model
{
    use HasAuditLogs;
    
    protected $fillable = [
        'transaksi_id',
        'barang_id',
        'jasa_id',
        'unit_id',          // NEW
        'tipe_item',        // barang | jasa
        'jumlah',
        'refunded_qty',     // jumlah yang sudah direfund (kumulatif)
        'harga_satuan',
        // diskon per item
        'discount_type',    // percent | amount | null
        'discount_value',
        'discount_amount',
        'discount_reason',
        'subtotal',
    ]; // :contentReference[oaicite:6]{index=6}

    protected $casts = [
        'refunded_qty' => 'integer',
    ];

    public function transaksi() { return $this->belongsTo(Transaksi::class); }
    public function barang()    { return $this->belongsTo(Barang::class);    }
    public function jasa()      { return $this->belongsTo(Jasa::class);      }
    public function unit()      { return $this->belongsTo(Unit::class);      }   // NEW
}
