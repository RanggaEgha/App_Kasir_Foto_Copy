<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Header transaksi: diskon invoice, kupon, alasan
        Schema::table('transaksis', function (Blueprint $t) {
            if (!Schema::hasColumn('transaksis','discount_type')) {
                $t->enum('discount_type', ['percent','amount'])->nullable()->after('payment_status');
            }
            if (!Schema::hasColumn('transaksis','discount_value')) {
                $t->unsignedInteger('discount_value')->default(0)->after('discount_type');
            }
            if (!Schema::hasColumn('transaksis','discount_amount')) {
                $t->unsignedBigInteger('discount_amount')->default(0)->after('discount_value');
            }
            if (!Schema::hasColumn('transaksis','discount_reason')) {
                $t->string('discount_reason')->nullable()->after('discount_amount');
            }
            if (!Schema::hasColumn('transaksis','coupon_code')) {
                $t->string('coupon_code')->nullable()->after('discount_reason');
            }
        });

        // Detail transaksi: diskon per item
        Schema::table('transaksi_items', function (Blueprint $t) {
            if (!Schema::hasColumn('transaksi_items','discount_type')) {
                $t->enum('discount_type', ['percent','amount'])->nullable()->after('harga_satuan');
            }
            if (!Schema::hasColumn('transaksi_items','discount_value')) {
                $t->unsignedInteger('discount_value')->default(0)->after('discount_type');
            }
            if (!Schema::hasColumn('transaksi_items','discount_amount')) {
                $t->unsignedBigInteger('discount_amount')->default(0)->after('discount_value');
            }
            if (!Schema::hasColumn('transaksi_items','discount_reason')) {
                $t->string('discount_reason')->nullable()->after('discount_amount');
            }
        });
    }

    public function down(): void
    {
        Schema::table('transaksis', function (Blueprint $t) {
            foreach (['coupon_code','discount_reason','discount_amount','discount_value','discount_type'] as $col) {
                if (Schema::hasColumn('transaksis', $col)) {
                    $t->dropColumn($col);
                }
            }
        });
        Schema::table('transaksi_items', function (Blueprint $t) {
            foreach (['discount_reason','discount_amount','discount_value','discount_type'] as $col) {
                if (Schema::hasColumn('transaksi_items', $col)) {
                    $t->dropColumn($col);
                }
            }
        });
    }
};

