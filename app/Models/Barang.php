<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    protected $fillable = ['nama','kategori','keterangan'];

    public function units()
    {
        return $this->belongsToMany(Unit::class,'barang_unit_prices')
                    ->withPivot(['harga','stok'])
                    ->withTimestamps();
    }

    /* stok total dalam pcs (for laporan) */
    public function stokPcs(): int
    {
        return $this->units->sum(fn($u)=> $u->pivot->stok * $u->konversi);
    }
}
