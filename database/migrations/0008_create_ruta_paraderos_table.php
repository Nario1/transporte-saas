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
        Schema::create('ruta_paraderos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ruta_id')->constrained()->cascadeOnDelete();
            $table->string('nombre', 120);
            $table->enum('tipo', ['origen', 'intermedio', 'destino'])->default('intermedio');
            $table->unsignedTinyInteger('orden')->default(0);
            $table->timestamps();

            $table->index('ruta_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ruta_paraderos');
    }
};
