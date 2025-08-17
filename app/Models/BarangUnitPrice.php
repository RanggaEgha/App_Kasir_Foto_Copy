<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BarangUnitPrice extends Model
{
    // Pastikan nama tabel benar (kalau perlu)
    protected $table = 'barang_unit_prices';

    protected $fillable = [
        'barang_id',
        'unit_id',
        'harga',
        'stok',
    ];

    // Relasi ke Barang & Unit
    public function barang()
    {
        return $this->belongsTo(Barang::class, 'barang_id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }
}
