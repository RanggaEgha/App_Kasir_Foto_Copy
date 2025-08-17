<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage; // <-- tambah

class Barang extends Model
{
    protected $fillable = [
        'nama','kategori','keterangan',
        'tipe_penjualan','satuan',
        'harga_satuan','harga_paket','isi_per_paket',
        'stok_satuan','stok_paket',
        'image_path', // <-- tambah
    ];

    public function units()
    {
        return $this->belongsToMany(Unit::class, 'barang_unit_prices')
                    ->withPivot(['harga','stok'])
                    ->withTimestamps();
    }

    public function stokPcs(): int
    {
        return $this->units->sum(fn($u) => $u->pivot->stok * $u->konversi);
    }

    // URL siap pakai untuk <img>
    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path ? Storage::url($this->image_path) : null;
    }
}
