<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        // Ubah enum & normalisasi data lama
        DB::statement("ALTER TABLE transaksis
            MODIFY COLUMN metode_bayar ENUM('cash','transfer','qris') NOT NULL DEFAULT 'cash'");
        DB::statement("UPDATE transaksis SET metode_bayar='transfer' WHERE metode_bayar IN ('debit','dana')");
    }
    public function down(): void {
        DB::statement("ALTER TABLE transaksis
            MODIFY COLUMN metode_bayar ENUM('cash','debit','dana') NOT NULL DEFAULT 'cash'");
    }
};
