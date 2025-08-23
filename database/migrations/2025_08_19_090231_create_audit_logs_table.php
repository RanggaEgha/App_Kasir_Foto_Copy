<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('batch_id')->index();             // grup dalam 1 request
            $table->string('event', 40)->index();          // created/updated/deleted/restored/custom
            $table->string('subject_type')->nullable();    // App\Models\Barang, dst.
            $table->unsignedBigInteger('subject_id')->nullable()->index();
            $table->unsignedBigInteger('actor_id')->nullable()->index(); // users.id
            $table->string('actor_name')->nullable();      // 'Zahra Anggun'
            $table->string('actor_role', 30)->nullable();  // 'kasir' / 'admin'
            $table->string('url')->nullable();
            $table->string('method', 10)->nullable();
            $table->string('ip', 45)->nullable();
            $table->text('user_agent')->nullable();

            // Perubahan data
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->json('properties')->nullable();        // info tambahan (amount, method, dsb)
            $table->string('description')->nullable();     // kalimat ringkas

            $table->timestamp('created_at')->useCurrent();
            $table->index(['subject_type','subject_id']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('audit_logs');
    }
};
