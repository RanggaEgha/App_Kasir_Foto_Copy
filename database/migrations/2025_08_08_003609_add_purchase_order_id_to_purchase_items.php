<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // 1) Tambah kolom jika belum ada
        Schema::table('purchase_items', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_items', 'purchase_order_id')) {
                $table->unsignedBigInteger('purchase_order_id')->nullable()->after('id');
            }
        });

        // 2) Kalau ada kolom lama (mis. purchase_id), salin datanya
        if (Schema::hasColumn('purchase_items', 'purchase_id')) {
            DB::statement("
                UPDATE purchase_items
                SET purchase_order_id = purchase_id
                WHERE purchase_order_id IS NULL
            ");
        }

        // 3) Tambah FK hanya jika belum ada FK untuk kolom ini
        $db = DB::getDatabaseName();
        $hasFk = DB::table('information_schema.KEY_COLUMN_USAGE')
            ->where('TABLE_SCHEMA', $db)
            ->where('TABLE_NAME', 'purchase_items')
            ->where('COLUMN_NAME', 'purchase_order_id')
            ->whereNotNull('REFERENCED_TABLE_NAME')
            ->exists();

        if (!$hasFk) {
            Schema::table('purchase_items', function (Blueprint $table) {
                // beri nama FK sendiri supaya gampang di-drop saat down()
                $table->index('purchase_order_id', 'purchase_items_po_idx');
                $table->foreign('purchase_order_id', 'purchase_items_po_fk')
                      ->references('id')->on('purchase_orders')
                      ->cascadeOnUpdate()
                      ->restrictOnDelete();
            });
        }
    }

    public function down(): void
    {
        $db = DB::getDatabaseName();
        $fkExists = DB::table('information_schema.KEY_COLUMN_USAGE')
            ->where('TABLE_SCHEMA', $db)
            ->where('TABLE_NAME', 'purchase_items')
            ->where('CONSTRAINT_NAME', 'purchase_items_po_fk')
            ->exists();

        Schema::table('purchase_items', function (Blueprint $table) use ($fkExists) {
            if ($fkExists) {
                $table->dropForeign('purchase_items_po_fk');
            }
            if (Schema::hasColumn('purchase_items', 'purchase_order_id')) {
                $table->dropIndex('purchase_items_po_idx');
                $table->dropColumn('purchase_order_id');
            }
        });
    }
};
