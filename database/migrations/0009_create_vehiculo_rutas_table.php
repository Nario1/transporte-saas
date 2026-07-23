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
        Schema::create('vehiculo_rutas', function (Blueprint $table) {
            $table->foreignId('vehiculo_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ruta_id')->constrained()->cascadeOnDelete();
            $table->boolean('activo')->default(true);
            $table->date('fecha_asignacion')->nullable();
            $table->primary(['vehiculo_id', 'ruta_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehiculo_rutas');
    }
};
