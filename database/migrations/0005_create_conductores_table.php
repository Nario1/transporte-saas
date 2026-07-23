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
        Schema::create('conductores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained()->cascadeOnDelete();
            $table->foreignId('propietario_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();         // conductor que también es propietario
            $table->string('nombre', 120);
            $table->string('apellidos', 120)->nullable();
            $table->string('dni', 8)->nullable();
            $table->string('telefono', 15)->nullable();
            $table->string('email')->nullable();
            $table->string('direccion')->nullable();
            $table->string('tipo_licencia', 10)->default('A-IIA');
            $table->date('licencia_vence')->nullable();
            $table->enum('estado', ['activo', 'suspendido', 'inactivo'])->default('activo');
            $table->text('notas')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('empresa_id');
            $table->index(['empresa_id', 'estado']);
            $table->index('propietario_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conductors');
    }
};
