<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('transaksis', function (Blueprint $table) {
            $table->index(['status','tanggal'], 'transaksis_status_tanggal_idx');
        });
        Schema::table('transaksi_items', function (Blueprint $table) {
            $table->index(['transaksi_id','barang_id','jasa_id'], 'transaksi_items_main_idx');
        });
    }

    public function down(): void
    {
        Schema::table('transaksis', function (Blueprint $table) {
            $table->dropIndex('transaksis_status_tanggal_idx');
        });
        Schema::table('transaksi_items', function (Blueprint $table) {
            $table->dropIndex('transaksi_items_main_idx');
        });
    }
};
