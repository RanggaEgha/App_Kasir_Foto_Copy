<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->unsignedBigInteger('unit_id')->nullable()->after('barang_id');
        });

        // Set default ke unit 'pcs' untuk data lama (jika ada)
        DB::statement("
            UPDATE purchase_items pi
            SET unit_id = (SELECT id FROM units WHERE kode = 'pcs' LIMIT 1)
            WHERE unit_id IS NULL
        ");

        Schema::table('purchase_items', function (Blueprint $table) {
            $table->unsignedBigInteger('unit_id')->nullable(false)->change();
            $table->foreign('unit_id')->references('id')->on('units')->cascadeOnUpdate()->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->dropColumn('unit_id');
        });
    }
};
