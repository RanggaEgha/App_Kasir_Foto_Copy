<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\HasAuditLogs;
use Illuminate\Support\Facades\Storage;

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
        'image_path',
    ];

    protected array $auditFiles = [
        'image_path' => 'public',
    ];

    protected $appends = ['image_url'];

    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path ? Storage::disk('public')->url($this->image_path) : null;
    }
}
