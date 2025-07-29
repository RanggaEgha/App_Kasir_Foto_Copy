<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BarangUnitPrice extends Model
{
    protected $fillable = ['barang_id','unit_id','stok','harga'];
}
