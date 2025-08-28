<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discount_rules', function (Blueprint $t) {
            $t->id();
            $t->enum('target_type', ['barang','jasa']);
            $t->unsignedBigInteger('target_id')->nullable(); // null → berlaku untuk semua target_type
            $t->unsignedInteger('min_qty')->default(1);
            $t->enum('discount_type', ['percent','amount']);
            $t->unsignedInteger('discount_value');
            $t->boolean('is_active')->default(true);
            $t->timestamps();

            $t->index(['target_type','target_id','min_qty']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discount_rules');
    }
};

