<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
{
   Schema::create('barangs', function (Blueprint $table) {
    $table->id();
    $table->string('nama');
    $table->string('kategori')->nullable(); // ATK, Fotokopi, dll
    $table->enum('tipe_penjualan', ['paket', 'satuan']); // jasa dipisahkan nanti
    $table->string('satuan')->default('pcs'); // pcs, kotak, lembar, dll

    $table->integer('harga_satuan')->nullable(); // harga per pcs
    $table->integer('harga_paket')->nullable(); // harga per kotak
    $table->integer('isi_per_paket')->nullable(); // isi 1 kotak = x pcs

    $table->integer('stok_satuan')->nullable(); // stok ecer
    $table->integer('stok_paket')->nullable(); // stok kotak

    $table->text('keterangan')->nullable(); // opsional
    $table->timestamps();
});

}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barangs');
    }
};
