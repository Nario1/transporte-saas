<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conductor_rostros', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conductor_id')->constrained('conductores')->cascadeOnDelete();
            $table->json('embedding');
            $table->string('foto_path');
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->index('conductor_id');
            $table->index('activo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conductor_rostros');
    }
};
