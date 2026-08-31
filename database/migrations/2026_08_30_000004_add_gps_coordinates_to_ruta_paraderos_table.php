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
        Schema::table('ruta_paraderos', function (Blueprint $table) {
            $table->decimal('latitud_a', 10, 7)->nullable()->after('orden');
            $table->decimal('longitud_a', 10, 7)->nullable()->after('latitud_a');
            $table->decimal('latitud_b', 10, 7)->nullable()->after('longitud_a');
            $table->decimal('longitud_b', 10, 7)->nullable()->after('latitud_b');
            $table->integer('tolerancia')->default(30)->after('longitud_b'); // tolerancia en metros
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ruta_paraderos', function (Blueprint $table) {
            $table->dropColumn(['latitud_a', 'longitud_a', 'latitud_b', 'longitud_b', 'tolerancia']);
        });
    }
};
