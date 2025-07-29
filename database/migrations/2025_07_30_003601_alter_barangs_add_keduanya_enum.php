<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Tambah value baru pada enum tipe_penjualan
        DB::statement("ALTER TABLE barangs
            MODIFY tipe_penjualan ENUM('satuan','paket','keduanya') NOT NULL
            AFTER kategori");
    }

    public function down(): void
    {
        // Revert ke enum dua nilai saja
        DB::statement("ALTER TABLE barangs
            MODIFY tipe_penjualan ENUM('satuan','paket') NOT NULL
            AFTER kategori");
    }
};
