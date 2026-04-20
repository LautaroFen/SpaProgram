<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

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

            $table->enum('method', ['cash', 'card', 'transfer', 'other'])->default('cash');
            $table->enum('status', ['pending', 'paid', 'void'])->default('paid');

            $table->dateTime('paid_at')->nullable();
            $table->string('reference', 120)->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['client_id', 'created_at']);
            $table->index('appointment_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
