<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kasir_shifts', function (Blueprint $t) {
            if (!Schema::hasColumn('kasir_shifts','cash_count')) {
                $t->json('cash_count')->nullable()->after('closing_cash'); // {100000:2,50000:1,...}
            }
            if (!Schema::hasColumn('kasir_shifts','method_breakdown')) {
                $t->json('method_breakdown')->nullable()->after('cash_count'); // {cash:..., transfer:..., qris:...}
            }
        });
    }

    public function down(): void
    {
        Schema::table('kasir_shifts', function (Blueprint $t) {
            if (Schema::hasColumn('kasir_shifts','method_breakdown')) $t->dropColumn('method_breakdown');
            if (Schema::hasColumn('kasir_shifts','cash_count')) $t->dropColumn('cash_count');
        });
    }
};

