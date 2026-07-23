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
        Schema::create('tributos', function (Blueprint $table) {
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

            $table->foreignId('cobrado_por')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete(); // usuario que cobró

            // ── Datos del tributo ──
            $table->date('fecha');
            $table->decimal('monto', 8, 2)->default(24.00);
            $table->enum('metodo_pago', ['efectivo', 'yape', 'plin', 'transferencia'])->nullable();
            $table->enum('estado', ['pagado', 'pendiente'])->default('pendiente');
            $table->timestamp('cobrado_at')->nullable();
            $table->text('observaciones')->nullable();

            // ── Timestamps y soft deletes ──
            $table->timestamps();
            $table->softDeletes();

            // ── Índices ──
            $table->index(['empresa_id', 'fecha']);
            $table->index(['vehiculo_id', 'fecha']);
            $table->index(['empresa_id', 'estado']);

            // ── Restricción única: 1 tributo/vehículo/día
            $table->unique(['vehiculo_id', 'fecha']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tributos');
    }
};