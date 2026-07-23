<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vueltas', function (Blueprint $table) {
            $table->decimal('latitud',  10, 7)->nullable()->after('hora_llegada');
            $table->decimal('longitud', 10, 7)->nullable()->after('latitud');
            $table->enum('estado', ['activa', 'completada', 'cancelada'])->default('completada')->after('longitud');
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::table('vueltas', function (Blueprint $table) {
            $table->dropColumn(['latitud', 'longitud', 'estado']);
        });
    }
};
