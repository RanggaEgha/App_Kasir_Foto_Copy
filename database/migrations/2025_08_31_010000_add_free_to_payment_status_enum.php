<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Tambah value 'free' ke enum payment_status pada tabel transaksis
        DB::statement("ALTER TABLE transaksis MODIFY payment_status ENUM('unpaid','partial','paid','free') NOT NULL DEFAULT 'unpaid'");
    }

    public function down(): void
    {
        // Konversi 'free' menjadi 'paid' sebelum revert enum
        DB::statement("UPDATE transaksis SET payment_status='paid' WHERE payment_status='free'");
        DB::statement("ALTER TABLE transaksis MODIFY payment_status ENUM('unpaid','partial','paid') NOT NULL DEFAULT 'unpaid'");
    }
};

