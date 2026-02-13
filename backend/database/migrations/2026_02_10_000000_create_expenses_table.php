<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();

            $table->string('category', 80);
            $table->string('payee', 160);

            $table->unsignedBigInteger('amount_due_cents');
            $table->unsignedBigInteger('amount_paid_cents')->default(0);

            $table->dateTime('performed_at');

            $table->timestamps();

            $table->index('performed_at');
            $table->index('category');
            $table->index('payee');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
