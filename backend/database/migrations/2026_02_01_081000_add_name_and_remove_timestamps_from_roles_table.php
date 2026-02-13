<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            if (! Schema::hasColumn('roles', 'name')) {
                $table->string('name', 50)->unique()->after('id');
            }

            if (Schema::hasColumn('roles', 'created_at')) {
                $table->dropColumn('created_at');
            }

            if (Schema::hasColumn('roles', 'updated_at')) {
                $table->dropColumn('updated_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            if (Schema::hasColumn('roles', 'name')) {
                $table->dropUnique(['name']);
                $table->dropColumn('name');
            }

            if (! Schema::hasColumn('roles', 'created_at')) {
                $table->timestamp('created_at')->nullable();
            }

            if (! Schema::hasColumn('roles', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }
        });
    }
};
