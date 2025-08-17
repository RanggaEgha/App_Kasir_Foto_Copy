<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransaksiItem extends Model
{
    protected $fillable = [
        'transaksi_id',
        'barang_id',
        'jasa_id',
        'unit_id',          // NEW
        'tipe_item',        // barang | jasa
        'jumlah',
        'harga_satuan',
        'subtotal',
    ]; // :contentReference[oaicite:6]{index=6}

    public function transaksi() { return $this->belongsTo(Transaksi::class); }
    public function barang()    { return $this->belongsTo(Barang::class);    }
    public function jasa()      { return $this->belongsTo(Jasa::class);      }
    public function unit()      { return $this->belongsTo(Unit::class);      }   // NEW
}
