<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
   protected $fillable = [
    'nama',
    'kategori',
    'tipe_penjualan',
    'satuan',
    'harga_satuan',
    'harga_paket',
    'isi_per_paket',
    'stok_satuan',
    'stok_paket',
    'keterangan'
];


    use HasFactory;
}
