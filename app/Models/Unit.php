<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\HasAuditLogs;

class Unit extends Model
{
    use HasAuditLogs;
    
    protected $fillable = ['kode','konversi'];

    public function barangs()
    {
        return $this->belongsToMany(Barang::class, 'barang_unit_prices')
                    ->withPivot(['harga','stok'])
                    ->withTimestamps();
    }
}
