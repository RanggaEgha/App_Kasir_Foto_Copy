<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaksi_items', function (Blueprint $t) {
            if (!Schema::hasColumn('transaksi_items', 'unit_id')) {
                $t->foreignId('unit_id')
                  ->after('barang_id')
                  ->nullable()
                  ->constrained('units')
                  ->nullOnDelete();
            }
            if (Schema::hasColumn('transaksi_items', 'tipe_qty')) {
                $t->dropColumn('tipe_qty');   // sudah tergantikan oleh unit_id
            }
        });
    }

    public function down(): void
    {
        Schema::table('transaksi_items', function (Blueprint $t) {
            if (Schema::hasColumn('transaksi_items', 'unit_id')) {
                $t->dropForeign(['unit_id']);
                $t->dropColumn('unit_id');
            }
            if (!Schema::hasColumn('transaksi_items', 'tipe_qty')) {
                $t->enum('tipe_qty', ['satuan', 'paket'])->nullable();
            }
        });
    }
};
