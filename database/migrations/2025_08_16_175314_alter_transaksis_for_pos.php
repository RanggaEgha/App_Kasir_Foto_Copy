<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('transaksis', function (Blueprint $t) {
            if (!Schema::hasColumn('transaksis','status')) {
                $t->enum('status',['draft','posted','void'])->default('draft');
            }
            if (!Schema::hasColumn('transaksis','payment_status')) {
                $t->enum('payment_status',['unpaid','partial','paid'])->default('unpaid');
            }
            if (!Schema::hasColumn('transaksis','total_harga')) {
                $t->unsignedBigInteger('total_harga')->default(0);
            }
            if (!Schema::hasColumn('transaksis','dibayar')) {
                $t->unsignedBigInteger('dibayar')->default(0);
            }
            if (!Schema::hasColumn('transaksis','kembalian')) {
                $t->unsignedBigInteger('kembalian')->default(0);
            }
            if (!Schema::hasColumn('transaksis','posted_at')) {
                $t->timestamp('posted_at')->nullable();
            }
            if (!Schema::hasColumn('transaksis','voided_at')) {
                $t->timestamp('voided_at')->nullable();
            }
            if (!Schema::hasColumn('transaksis','void_reason')) {
                $t->string('void_reason')->nullable();
            }
            if (!Schema::hasColumn('transaksis','shift_id')) {
                $t->foreignId('shift_id')->nullable()->constrained('kasir_shifts')->nullOnDelete();
            }
        });
    }

    public function down(): void {
        Schema::table('transaksis', function (Blueprint $t) {
            // Tidak perlu drop kolom saat rollback (hindari kehilangan data produksi)
            // Jika ingin, boleh manual di sini.
        });
    }
};
