<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use App\Models\Concerns\HasAuditLogs;

class Barang extends Model
{
    use HasAuditLogs;

    protected $fillable = [
        'nama','kategori','keterangan',
        'tipe_penjualan','satuan',
        'harga_satuan','harga_paket','isi_per_paket',
        'stok_satuan','stok_paket',
        'image_path', // kolom file gambar
    ];

    /**
     * Daftar atribut file yang ingin disnapshot di audit log.
     * Key = nama kolom file; Value = disk penyimpanan (sesuai config/filesystems.php)
     */
    protected array $auditFiles = [
        'image_path' => 'public',
    ];

    /**
     * Otomatis sertakan image_url saat model di-serialize (opsional).
     */
    protected $appends = ['image_url'];

    // ================== RELATIONS ==================

    public function units()
    {
        return $this->belongsToMany(Unit::class, 'barang_unit_prices')
                    ->withPivot(['harga','stok'])
                    ->withTimestamps();
    }

    // ================== HELPERS ==================

    /**
     * Total stok dalam satuan PCS (menjumlahkan stok tiap unit * konversi).
     */
    public function stokPcs(): int
    {
        $units = $this->relationLoaded('units') ? $this->units : $this->units()->get();

        return (int) $units->sum(function ($u) {
            // pastikan integer
            $stok = (int) ($u->pivot->stok ?? 0);
            $konv = (int) ($u->konversi ?? 1);
            return $stok * $konv;
        });
    }

    /**
     * URL siap pakai untuk <img>.
     */
    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path ? Storage::url($this->image_path) : null;
    }
}
