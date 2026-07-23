<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // En SQLite y otros motores, modificar un enum puede ser complejo. 
        // Dado que usamos MariaDB/MySQL usualmente, usaremos una sentencia nativa o el cambio de columna.
        // Si el driver es sqlite, simplemente permitiremos el cambio.
        
        Schema::table('tributos', function (Blueprint $table) {
            $table->string('estado', 20)->default('pendiente')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tributos', function (Blueprint $table) {
            $table->enum('estado', ['pagado', 'pendiente'])->default('pendiente')->change();
        });
    }
};
