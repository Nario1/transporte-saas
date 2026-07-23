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
        Schema::create('paradero_checkins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('conductor_id')->constrained('conductores')->cascadeOnDelete();
            $table->foreignId('vehiculo_id')->constrained('vehiculos')->cascadeOnDelete();
            $table->foreignId('ruta_paradero_id')->constrained('ruta_paraderos')->cascadeOnDelete();
            $table->foreignId('vuelta_id')->nullable()->constrained('vueltas')->nullOnDelete();
            $table->dateTime('hora_registro');
            $table->string('tipo', 30)->default('intermedio'); // inicio, fin, intermedio
            $table->boolean('exitoso')->default(true);
            $table->string('observaciones')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paradero_checkins');
    }
};
