<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();

            // relasi ke suppliers
            $table->foreignId('supplier_id')
                  ->constrained()
                  ->cascadeOnDelete();

            // informasi dasar PO
            $table->string('invoice_no')->unique();
            $table->timestamp('purchase_date')
                  ->default(DB::raw('CURRENT_TIMESTAMP'));

            // nilai uang
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('tax',      12, 2)->default(0);
            $table->decimal('total',    12, 2);

            // pembayaran
            $table->enum('payment_method', ['cash', 'transfer', 'qris', 'credit'])
                  ->default('cash');
            $table->decimal('amount_paid',   12, 2)->nullable();
            $table->decimal('change_amount', 12, 2)->nullable();

            // status PO
            $table->enum('status', ['draft', 'received', 'paid'])
                  ->default('draft');

            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
