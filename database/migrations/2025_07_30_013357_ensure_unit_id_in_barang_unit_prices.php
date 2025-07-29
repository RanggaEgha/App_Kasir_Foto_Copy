<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('barang_unit_prices', function (Blueprint $t) {
            if (!Schema::hasColumn('barang_unit_prices', 'unit_id')) {
                $t->foreignId('unit_id')
                  ->after('barang_id')
                  ->constrained('units')
                  ->cascadeOnDelete();
                $t->unique(['barang_id','unit_id']);
            }
        });
    }
    public function down(): void
    {
        Schema::table('barang_unit_prices', function (Blueprint $t) {
            if (Schema::hasColumn('barang_unit_prices', 'unit_id')) {
                $t->dropForeign(['unit_id']);
                $t->dropColumn('unit_id');
            }
        });
    }
};
