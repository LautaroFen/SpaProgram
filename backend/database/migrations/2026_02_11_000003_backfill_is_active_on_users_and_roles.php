<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'is_active')) {
            DB::table('users')->update(['is_active' => true]);
        }

        if (Schema::hasTable('roles') && Schema::hasColumn('roles', 'is_active')) {
            DB::table('roles')->update(['is_active' => true]);
        }
    }

    public function down(): void
    {
        // Intentionally no-op.
    }
};
