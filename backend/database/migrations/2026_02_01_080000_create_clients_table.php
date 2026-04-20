<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();

            // Unique public identifier for loyalty/discounts even if DNI is missing.
            $table->char('code', 26)->unique();

            $table->string('dni', 32)->nullable()->unique();

            $table->string('first_name', 100);
            $table->string('last_name', 100);

            $table->string('email')->nullable();
            $table->string('phone', 30);

            // Total outstanding balance across all appointments.
            $table->unsignedBigInteger('balance_cents')->default(0);

            $table->timestamps();

            $table->index(['last_name', 'first_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
