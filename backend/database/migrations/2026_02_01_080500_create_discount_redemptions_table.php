<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discount_redemptions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('discount_id')
                ->constrained('discounts')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('client_id')
                ->constrained('clients')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('appointment_id')
                ->nullable()
                ->constrained('appointments')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->unsignedInteger('amount_cents');
            $table->dateTime('redeemed_at');
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['client_id', 'redeemed_at']);
            $table->index(['discount_id', 'redeemed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discount_redemptions');
    }
};
