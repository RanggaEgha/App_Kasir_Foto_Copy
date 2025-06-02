<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaksi_items', function (Blueprint $table) {
            $table->bigIncrements('id');

            // Header transaksi
            $table->foreignId('transaksi_id')
                  ->constrained('transaksis')
                  ->onDelete('cascade');

            $table->foreignId('barang_id')
                  ->nullable()
                  ->constrained('barangs')
                  ->nullOnDelete();         // Laravel ≥10, set NULL saat barang dihapus

            $table->foreignId('jasa_id')
                  ->nullable()
                  ->constrained('jasas')
                  ->nullOnDelete();

            // Penanda jenis item
            $table->enum('tipe_item', ['barang', 'jasa']);

            // Detail pembelian
            $table->unsignedInteger('jumlah');
            $table->unsignedInteger('harga_satuan');
            $table->unsignedInteger('subtotal');

            $table->timestamps();

            // Index tambahan (opsional tapi membantu query)
            $table->index(['transaksi_id', 'tipe_item']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaksi_items');
    }
};
