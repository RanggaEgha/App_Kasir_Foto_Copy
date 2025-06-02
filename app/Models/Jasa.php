<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jasa extends Model
{
    use HasFactory;
    protected $fillable = ['nama', 'jenis', 'satuan', 'harga_per_satuan', 'keterangan'];

}
