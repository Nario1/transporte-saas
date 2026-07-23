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
        Schema::create('ajustes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained()->cascadeOnDelete();

            $table->string('clave', 80);              // Ej: modulo.tributos.activo
            $table->text('valor')->nullable();         // JSON, string, bool, number
            $table->string('tipo', 20)->default('string'); // string|boolean|integer|json
            $table->string('grupo', 40)->default('general'); // general|modulos|permisos|notif
            $table->string('etiqueta', 100)->nullable(); // Label legible
            $table->text('descripcion')->nullable();
            $table->boolean('es_publico')->default(false); // visible en UI sin ser admin
            $table->timestamps();

            $table->unique(['empresa_id', 'clave']);
            $table->index(['empresa_id', 'grupo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ajustes');
    }
};
