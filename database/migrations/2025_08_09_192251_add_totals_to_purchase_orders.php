<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('purchase_orders', function (Blueprint $t) {
            if (!Schema::hasColumn('purchase_orders','subtotal'))
                $t->decimal('subtotal',14,2)->default(0)->after('supplier_id');
            if (!Schema::hasColumn('purchase_orders','discount'))
                $t->decimal('discount',14,2)->default(0)->after('subtotal'); // nominal
            if (!Schema::hasColumn('purchase_orders','tax_percent'))
                $t->decimal('tax_percent',5,2)->default(0)->after('discount'); // %
            if (!Schema::hasColumn('purchase_orders','tax_amount'))
                $t->decimal('tax_amount',14,2)->default(0)->after('tax_percent');
            if (!Schema::hasColumn('purchase_orders','grand_total'))
                $t->decimal('grand_total',14,2)->default(0)->after('tax_amount');
        });
    }

    public function down(): void {
        Schema::table('purchase_orders', function (Blueprint $t) {
            foreach (['grand_total','tax_amount','tax_percent','discount','subtotal'] as $c) {
                if (Schema::hasColumn('purchase_orders',$c)) $t->dropColumn($c);
            }
        });
    }
};
