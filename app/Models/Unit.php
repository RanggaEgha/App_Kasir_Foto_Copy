<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    protected $fillable = ['kode','konversi'];

    /* relasi many-to-many (pivot berisi harga & stok) */
    public function barangs()
    {
        return $this->belongsToMany(Barang::class,'barang_unit_prices')
                    ->withPivot(['harga','stok'])
                    ->withTimestamps();
    }
}
