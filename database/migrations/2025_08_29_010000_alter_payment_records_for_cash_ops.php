<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_records', function (Blueprint $t) {
            // Tambah kolom note (opsional), tanpa mengubah foreign key
            if (!Schema::hasColumn('payment_records','note')) {
                $t->string('note')->nullable()->after('reference');
            }
        });
    }

    public function down(): void
    {
        Schema::table('payment_records', function (Blueprint $t) {
            // tidak kembalikan ke NOT NULL agar aman; hanya drop kolom note bila ada
            if (Schema::hasColumn('payment_records','note')) {
                $t->dropColumn('note');
            }
        });
    }
};
