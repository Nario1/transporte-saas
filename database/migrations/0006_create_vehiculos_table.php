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
        Schema::create('vehiculos', function (Blueprint $table) {
            $table->id();

            // ── FK Empresa ──
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();

            // ── FK Propietario ──
            $table->foreignId('propietario_id')
                ->nullable()
                ->constrained('propietarios')
                ->nullOnDelete();

            // ── FK Conductor ──
            $table->foreignId('conductor_id')
                ->nullable()
                ->constrained('conductores')
                ->nullOnDelete();

            // ── Identificación ──
            $table->string('placa', 8)->unique();
            $table->unsignedSmallInteger('numero_flota')->nullable();
            $table->string('marca', 60)->nullable();
            $table->string('modelo', 60)->nullable();
            $table->string('color', 40)->nullable();
            $table->unsignedSmallInteger('anio')->nullable();

            // ── Documentos ──
            $table->date('soat_vence')->nullable();
            $table->date('rev_tecnica_vence')->nullable();
            $table->date('tarjeta_prop_vence')->nullable();

            // ── Estado ──
            $table->enum('estado', ['activo', 'inactivo', 'sin_salir', 'mantenimiento'])
                ->default('activo');

            $table->text('notas')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // ── Índices ──
            $table->index('empresa_id');
            $table->index(['empresa_id', 'estado']);
            $table->index('conductor_id');
            $table->index('propietario_id');
            $table->index(['empresa_id', 'numero_flota']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehiculos');
    }
};