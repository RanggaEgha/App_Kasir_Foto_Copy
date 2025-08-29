<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaksi_items', function (Blueprint $t) {
            if (!Schema::hasColumn('transaksi_items', 'refunded_qty')) {
                $t->unsignedInteger('refunded_qty')->default(0)->after('jumlah');
            }
        });
    }

    public function down(): void
    {
        Schema::table('transaksi_items', function (Blueprint $t) {
            if (Schema::hasColumn('transaksi_items', 'refunded_qty')) {
                $t->dropColumn('refunded_qty');
            }
        });
    }
};

