<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('payment_records', function (Blueprint $t) {
            $t->id();
            $t->foreignId('transaksi_id')->constrained()->cascadeOnDelete();
            $t->enum('direction', ['in','out'])->default('in'); // refund = out
            $t->enum('method', ['cash','transfer','qris']);
            $t->unsignedBigInteger('amount');
            $t->string('reference')->nullable();
            $t->timestamp('paid_at')->useCurrent();
            $t->foreignId('shift_id')->nullable()->constrained('kasir_shifts')->nullOnDelete();
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('payment_records');
    }
};
