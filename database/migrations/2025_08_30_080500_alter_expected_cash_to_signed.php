<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Make expected_cash signed BIGINT so it can store negatives when needed
        DB::statement("ALTER TABLE `kasir_shifts` MODIFY `expected_cash` BIGINT NOT NULL DEFAULT 0");
    }

    public function down(): void
    {
        // Revert to UNSIGNED BIGINT (original schema)
        DB::statement("ALTER TABLE `kasir_shifts` MODIFY `expected_cash` BIGINT UNSIGNED NOT NULL DEFAULT 0");
    }
};

