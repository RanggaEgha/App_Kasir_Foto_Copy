<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransaksiItem extends Model
{
    protected $fillable = [
        'transaksi_id',
        'barang_id',
        'jasa_id',
        'tipe_item',   // barang | jasa
        'tipe_qty',    // satuan | paket
        'jumlah',
        'harga_satuan',
        'subtotal',
    ];

    public function transaksi() { return $this->belongsTo(Transaksi::class); }
    public function barang()    { return $this->belongsTo(Barang::class);    }
    public function jasa()      { return $this->belongsTo(Jasa::class);      }
}
