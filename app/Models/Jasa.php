<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\HasAuditLogs;

class Jasa extends Model
{
    use HasFactory;
    use HasAuditLogs;

    // kolom yang memang ada di tabel `jasas`
    protected $fillable = [
        'nama',
        'jenis',
        'satuan',
        'harga_per_satuan',
        'keterangan',
    ];
}
