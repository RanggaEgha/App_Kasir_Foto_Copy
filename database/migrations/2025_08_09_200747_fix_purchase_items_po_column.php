<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 0) Samakan nilai: kalau purchase_order_id masih NULL, isi dari purchase_id
        DB::statement("
            UPDATE purchase_items
            SET purchase_order_id = purchase_id
            WHERE purchase_order_id IS NULL
        ");

        // 1) Lepas FK dengan NAMA YANG BENAR (sesuai SHOW CREATE TABLE)
        try { DB::statement('ALTER TABLE `purchase_items` DROP FOREIGN KEY `purchase_items_po_fk`'); } catch (\Throwable $e) {}
        try { DB::statement('ALTER TABLE `purchase_items` DROP FOREIGN KEY `purchase_items_purchase_id_foreign`'); } catch (\Throwable $e) {}

        // 2) (Opsional) lepas index manual kalau ada
        try { DB::statement('ALTER TABLE `purchase_items` DROP INDEX `purchase_items_po_idx`'); } catch (\Throwable $e) {}

        // 3) Hapus kolom lama `purchase_id`
        if (Schema::hasColumn('purchase_items','purchase_id')) {
            Schema::table('purchase_items', function (Blueprint $t) {
                $t->dropColumn('purchase_id');
            });
        }

        // 4) Wajibkan purchase_order_id NOT NULL
        DB::statement('ALTER TABLE `purchase_items` MODIFY `purchase_order_id` BIGINT UNSIGNED NOT NULL');

        // 5) Pasang FK baru dengan CASCADE
        Schema::table('purchase_items', function (Blueprint $t) {
            $t->foreign('purchase_order_id', 'purchase_items_po_fk')
              ->references('id')->on('purchase_orders')
              ->onUpdate('cascade')
              ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        // Lepas FK baru
        try { DB::statement('ALTER TABLE `purchase_items` DROP FOREIGN KEY `purchase_items_po_fk`'); } catch (\Throwable $e) {}

        // Kembalikan nullable
        DB::statement('ALTER TABLE `purchase_items` MODIFY `purchase_order_id` BIGINT UNSIGNED NULL');

        // Tambah balik kolom purchase_id + FK sederhana
        Schema::table('purchase_items', function (Blueprint $t) {
            if (!Schema::hasColumn('purchase_items','purchase_id')) {
                $t->unsignedBigInteger('purchase_id')->after('purchase_order_id');
            }
        });
        try {
            DB::statement('ALTER TABLE `purchase_items` ADD CONSTRAINT `purchase_items_purchase_id_foreign` FOREIGN KEY (`purchase_id`) REFERENCES `purchase_orders`(`id`) ON DELETE CASCADE');
        } catch (\Throwable $e) {}
    }
};
