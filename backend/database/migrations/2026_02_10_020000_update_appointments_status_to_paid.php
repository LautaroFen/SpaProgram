<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1) Temporarily extend enum to allow the new value.
        DB::statement("ALTER TABLE appointments MODIFY status ENUM('scheduled','completed','paid','cancelled','no_show') NOT NULL DEFAULT 'scheduled'");

        // 2) Convert legacy value.
        DB::statement("UPDATE appointments SET status = 'paid' WHERE status = 'completed'");

        // 3) Tighten enum to the final set.
        DB::statement("ALTER TABLE appointments MODIFY status ENUM('scheduled','paid','cancelled','no_show') NOT NULL DEFAULT 'scheduled'");
    }

    public function down(): void
    {
        // 1) Temporarily extend enum to allow both values.
        DB::statement("ALTER TABLE appointments MODIFY status ENUM('scheduled','completed','paid','cancelled','no_show') NOT NULL DEFAULT 'scheduled'");

        // 2) Restore legacy value.
        DB::statement("UPDATE appointments SET status = 'completed' WHERE status = 'paid'");

        // 3) Tighten enum to the original set.
        DB::statement("ALTER TABLE appointments MODIFY status ENUM('scheduled','completed','cancelled','no_show') NOT NULL DEFAULT 'scheduled'");
    }
};
