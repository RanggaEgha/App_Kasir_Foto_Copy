<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('kasir_shifts', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $t->timestamp('opened_at')->useCurrent();
            $t->unsignedBigInteger('opening_cash')->default(0);
            $t->timestamp('closed_at')->nullable();
            $t->unsignedBigInteger('closing_cash')->nullable();
            $t->unsignedBigInteger('expected_cash')->default(0);
            $t->bigInteger('difference')->default(0); // closing - expected
            $t->enum('status',['open','closed'])->default('open');
            $t->text('notes')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('kasir_shifts');
    }
};
