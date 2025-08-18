<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends \Illuminate\Database\Migrations\Migration {
  public function up(): void {
    Schema::table('users', function (Blueprint $t) {
      if (!Schema::hasColumn('users','role')) $t->enum('role',['admin','kasir'])->default('kasir')->after('email');
      if (!Schema::hasColumn('users','is_active')) $t->boolean('is_active')->default(true)->after('role');
    });
  }
  public function down(): void {
    Schema::table('users', fn(Blueprint $t) => $t->dropColumn(['role','is_active']));
  }
};
