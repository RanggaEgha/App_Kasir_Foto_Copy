<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
      Schema::create('jasas', function (Blueprint $table) {
    $table->id();
    $table->string('nama');
    $table->string('jenis')->nullable(); // misal: Print, Fotokopi, Penjilidan
    $table->string('satuan'); // misal: lembar, halaman
    $table->integer('harga_per_satuan');
    $table->text('keterangan')->nullable();
    $table->timestamps();
});


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jasas');
    }
};
