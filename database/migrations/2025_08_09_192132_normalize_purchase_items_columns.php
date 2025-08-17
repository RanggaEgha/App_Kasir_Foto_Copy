<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('purchase_items', function (Blueprint $t) {
            // Pastikan kolom inti yang kita pakai ada.
            if (!Schema::hasColumn('purchase_items','qty')) {
                $t->integer('qty')->default(0)->after('unit_id');
            }
            if (!Schema::hasColumn('purchase_items','unit_price')) {
                $t->decimal('unit_price',14,2)->default(0)->after('qty');
            }
            if (!Schema::hasColumn('purchase_items','subtotal')) {
                $t->decimal('subtotal',14,2)->default(0)->after('unit_price');
            }

            // (Opsional) kalau ada kolom lama yang membingungkan, hapus.
            if (Schema::hasColumn('purchase_items','quantity')) $t->dropColumn('quantity');
            if (Schema::hasColumn('purchase_items','price'))    $t->dropColumn('price');
        });
    }

    public function down(): void {
        Schema::table('purchase_items', function (Blueprint $t) {
            // rollback minimal
            if (Schema::hasColumn('purchase_items','subtotal'))   $t->dropColumn('subtotal');
            if (Schema::hasColumn('purchase_items','unit_price')) $t->dropColumn('unit_price');
            if (Schema::hasColumn('purchase_items','qty'))        $t->dropColumn('qty');
            // t->integer('quantity'); t->decimal('price',14,2); // jika perlu
        });
    }
};
