<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagos_mp', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tributo_id')->constrained('tributos')->cascadeOnDelete();
            $table->string('preference_id')->nullable();
            $table->string('payment_id')->nullable();
            $table->enum('estado', ['pendiente', 'aprobado', 'rechazado', 'cancelado', 'en_proceso'])->default('pendiente');
            $table->decimal('monto', 8, 2);
            $table->string('metodo')->nullable();
            $table->json('webhook_data')->nullable();
            $table->timestamps();
            $table->index('tributo_id');
            $table->index('preference_id');
            $table->index('payment_id');
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos_mp');
    }
};
