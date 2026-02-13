<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            // Default Laravel index name for `$table->char('code')->unique()` is `clients_code_unique`.
            $table->dropUnique('clients_code_unique');
            $table->dropColumn('code');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            // Allow NULL on rollback so existing rows don't require backfilled codes.
            $table->char('code', 26)->nullable()->unique();
        });
    }
};
