<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_records', function (Blueprint $t) {
            if (!Schema::hasColumn('payment_records', 'transaksi_id')) {
                $t->foreignId('transaksi_id')->nullable()->after('id')
                  ->constrained('transaksis')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('payment_records', function (Blueprint $t) {
            if (Schema::hasColumn('payment_records','transaksi_id')) {
                $t->dropForeign(['transaksi_id']);
                $t->dropColumn('transaksi_id');
            }
        });
    }
};

