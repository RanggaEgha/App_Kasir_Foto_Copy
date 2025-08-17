<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('barangs', function (Blueprint $t) {
            foreach ([
                'tipe_penjualan','satuan','harga_satuan','harga_paket',
                'isi_per_paket','stok_satuan','stok_paket'
            ] as $col) {
                if (Schema::hasColumn('barangs', $col)) $t->dropColumn($col);
            }
        });
    }
    public function down(): void
    {
        // cukup tambahkan ulang kolom string‐kosong jika rollback
        Schema::table('barangs', function (Blueprint $t) {
            $t->string('satuan')->nullable();
        });
    }
};
