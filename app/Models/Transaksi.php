<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Transaksi extends Model
{
    /* ——— kolom yang boleh mass-assign ——— */
    protected $fillable = [
        'kode_transaksi',
        'tanggal',
        'total_harga',
        'metode_bayar',
        'dibayar',
        'kembalian',
    ];

    /* ——— cast tanggal ke Carbon otomatis ——— */
    protected $casts = [
        'tanggal' => 'datetime',
    ];

    /* ========= RELASI ========= */
    public function items()
    {
        return $this->hasMany(TransaksiItem::class);
    }
}
