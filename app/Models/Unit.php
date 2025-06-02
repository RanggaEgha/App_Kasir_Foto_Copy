<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    protected $fillable = ['kode','konversi'];

    public function barangs()
    {
        return $this->belongsToMany(Barang::class, 'barang_unit_prices')
                    ->withPivot(['harga','stok'])->withTimestamps();
    }
}

