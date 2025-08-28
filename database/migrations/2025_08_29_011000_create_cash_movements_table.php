<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_movements', function (Blueprint $t) {
            $t->id();
            $t->foreignId('shift_id')->constrained('kasir_shifts')->cascadeOnDelete();
            $t->enum('direction', ['in','out']);
            $t->unsignedBigInteger('amount');
            $t->string('reference')->nullable();
            $t->string('note')->nullable();
            $t->timestamp('occurred_at')->useCurrent();
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_movements');
    }
};

