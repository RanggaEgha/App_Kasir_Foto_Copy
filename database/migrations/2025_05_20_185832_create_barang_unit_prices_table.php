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
        Schema::create('barang_unit_prices', function (Blueprint $t) {
            $t->id();
            $t->foreignId('barang_id')->constrained()->cascadeOnDelete();
            $t->foreignId('unit_id')->constrained()->cascadeOnDelete();
            $t->integer('harga');          // harga per unit
            $t->integer('stok')->default(0);
            $t->timestamps();

            $t->unique(['barang_id','unit_id']);
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barang_unit_prices');
    }
};
