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
        Schema::create('empresas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 120);
            $table->string('ruc', 11)->unique()->nullable();
            $table->string('razon_social', 160)->nullable();
            $table->string('telefono', 15)->nullable();
            $table->string('direccion')->nullable();
            $table->enum('plan', ['basico', 'pro', 'enterprise'])->default('basico');
            $table->boolean('activa')->default(true);
            $table->string('logo_path')->nullable();
            $table->decimal('tributo_diario', 8, 2)->default(0.00); // monto tributo configurable
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empresas');
    }
};
