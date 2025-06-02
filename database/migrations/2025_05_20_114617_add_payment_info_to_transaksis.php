<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaksis', function (Blueprint $table) {
            $table->enum('metode_bayar', ['cash','debit','dana'])
                  ->after('total_harga')->default('cash');
            $table->unsignedInteger('dibayar')->after('metode_bayar')->default(0);
            $table->unsignedInteger('kembalian')->after('dibayar')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('transaksis', function (Blueprint $table) {
            $table->dropColumn(['metode_bayar', 'dibayar', 'kembalian']);
        });
    }
};
