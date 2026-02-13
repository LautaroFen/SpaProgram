<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')
                ->after('id')
                ->constrained('roles')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->string('first_name', 100)->after('role_id');
            $table->string('last_name', 100)->after('first_name');

            $table->string('job_title', 120)->nullable()->after('last_name');

            $table->json('work_schedule')->nullable()->after('job_title');

            $table->dropColumn('name');
        });

        // Make login fields optional without requiring doctrine/dbal.
        // MySQL allows multiple NULL values with a UNIQUE index.
        DB::statement('ALTER TABLE `users` MODIFY `email` VARCHAR(255) NULL');
        DB::statement('ALTER TABLE `users` MODIFY `password` VARCHAR(255) NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('name');

            $table->dropForeign(['role_id']);
            $table->dropColumn('role_id');

            $table->dropColumn('first_name');
            $table->dropColumn('last_name');
            $table->dropColumn('job_title');
            $table->dropColumn('work_schedule');
        });

        DB::statement('ALTER TABLE `users` MODIFY `email` VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE `users` MODIFY `password` VARCHAR(255) NOT NULL');
    }
};
