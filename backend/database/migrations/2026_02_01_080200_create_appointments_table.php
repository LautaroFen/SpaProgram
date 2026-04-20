<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('client_id')
                ->constrained('clients')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            // Employee/professional assigned to the appointment
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('service_id')
                ->constrained('services')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->dateTime('start_at');
            $table->dateTime('end_at');

            // Price of this appointment at booking time
            $table->unsignedInteger('price_cents');

            // Prepayment / advance payment
            $table->unsignedInteger('deposit_cents')->default(0);

            // Optional snapshot so you can see the client's previous balance at time of booking
            $table->unsignedBigInteger('client_balance_before_cents')->nullable();

            $table->enum('status', ['scheduled', 'completed', 'cancelled', 'no_show'])->default('scheduled');
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index('start_at');
            $table->index(['user_id', 'start_at']);
            $table->index(['client_id', 'start_at']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
