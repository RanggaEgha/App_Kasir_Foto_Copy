<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_orders', 'tanggal')) {
                // nullable aman untuk strict mode; controller tetap mengisi nilainya
                $table->date('tanggal')->nullable()->after('invoice_no');
            }
            if (!Schema::hasColumn('purchase_orders', 'metode_bayar')) {
                $table->enum('metode_bayar', ['tunai','transfer','tempo'])
                      ->default('tunai')
                      ->after('tanggal');
            }
            if (!Schema::hasColumn('purchase_orders', 'total')) {
                $table->bigInteger('total')->default(0)->after('metode_bayar');
            }
        });
    }

    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_orders', 'tanggal')) {
                $table->dropColumn('tanggal');
            }
            if (Schema::hasColumn('purchase_orders', 'metode_bayar')) {
                $table->dropColumn('metode_bayar');
            }
            if (Schema::hasColumn('purchase_orders', 'total')) {
                $table->dropColumn('total');
            }
        });
    }
};
