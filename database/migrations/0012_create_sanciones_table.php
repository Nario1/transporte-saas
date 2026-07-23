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
        Schema::create('sanciones', function (Blueprint $table) {
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

            $table->foreignId('registrado_por')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('cobrado_por')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // ── Datos de la sanción ──
            $table->date('fecha');
            $table->string('motivo', 200);
            $table->text('descripcion')->nullable();
            $table->decimal('monto', 8, 2)->default(0);
            $table->enum('estado', ['pendiente', 'pagado'])->default('pendiente');
            $table->timestamp('cobrado_at')->nullable();

            // ── Timestamps y soft deletes ──
            $table->timestamps();
            $table->softDeletes();

            // ── Índices ──
            $table->index(['empresa_id', 'fecha']);
            $table->index(['empresa_id', 'estado']);
            $table->index('conductor_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sanciones');
    }
};