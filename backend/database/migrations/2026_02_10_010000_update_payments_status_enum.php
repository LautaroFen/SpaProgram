<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Convert legacy statuses before tightening the enum.
        DB::statement("UPDATE payments SET status = 'partial' WHERE status = 'pending'");

        // Keep 'void' for backward compatibility, but new UI will only create partial/paid.
        DB::statement("ALTER TABLE payments MODIFY status ENUM('partial','paid','void') NOT NULL DEFAULT 'paid'");
    }

    public function down(): void
    {
        DB::statement("UPDATE payments SET status = 'pending' WHERE status = 'partial'");
        DB::statement("ALTER TABLE payments MODIFY status ENUM('pending','paid','void') NOT NULL DEFAULT 'paid'");
    }
};
