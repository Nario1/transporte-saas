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
        Schema::create('alertas_operativos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->foreignId('conductor_id')->nullable()->constrained('conductores')->onDelete('set null');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null'); // Si es admin
            $table->string('punto'); // 'Punto A', 'Punto B', 'Punto C'
            $table->string('estado')->default('activo'); // 'activo', 'finalizado'
            $table->dateTime('expires_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alertas_operativos');
    }
};
