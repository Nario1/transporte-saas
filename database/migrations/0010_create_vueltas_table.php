<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('vueltas', function (Blueprint $table) {
            $table->id();

            // ── FKs ──
            $table->foreignId('empresa_id')
                ->constrained('empresas')
                ->cascadeOnDelete();

            $table->foreignId('vehiculo_id')
                ->constrained('vehiculos')
                ->cascadeOnDelete();

            $table->foreignId('conductor_id')
                ->nullable()
                ->constrained('conductores')
                ->nullOnDelete();

            $table->foreignId('ruta_id')
                ->nullable()
                ->constrained('rutas')
                ->nullOnDelete();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete(); // quién registró

            // ── Datos de la vuelta ──
            $table->date('fecha');
            $table->unsignedTinyInteger('numero_vuelta')->default(1);
            $table->time('hora_salida')->nullable();
            $table->time('hora_llegada')->nullable();
            $table->text('observaciones')->nullable();

            // ── Timestamps y soft deletes ──
            $table->timestamps();
            $table->softDeletes();

            // ── Índices ──
            $table->index(['empresa_id', 'fecha']);
            $table->index(['vehiculo_id', 'fecha']);
            $table->index('conductor_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vueltas');
    }
};