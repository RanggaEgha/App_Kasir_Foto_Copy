<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('purchase_items', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_items', 'qty')) {
                $table->unsignedInteger('qty')->default(0)->after('unit_id');
            }
        });

        // Jika ada kolom lama 'jumlah' / 'quantity', salin ke 'qty' sekali ini
        if (Schema::hasColumn('purchase_items', 'jumlah')) {
            DB::statement("UPDATE purchase_items SET qty = jumlah WHERE qty = 0 OR qty IS NULL");
        } elseif (Schema::hasColumn('purchase_items', 'quantity')) {
            DB::statement("UPDATE purchase_items SET qty = quantity WHERE qty = 0 OR qty IS NULL");
        }
    }

    public function down(): void
    {
        Schema::table('purchase_items', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_items', 'qty')) {
                $table->dropColumn('qty');
            }
        });
    }
};
