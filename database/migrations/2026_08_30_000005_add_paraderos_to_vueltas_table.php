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
        Schema::table('vueltas', function (Blueprint $table) {
            $table->foreignId('paradero_salida_id')->nullable()->after('ruta_id')->constrained('ruta_paraderos')->nullOnDelete();
            $table->foreignId('paradero_llegada_id')->nullable()->after('paradero_salida_id')->constrained('ruta_paraderos')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vueltas', function (Blueprint $table) {
            $table->dropForeign(['paradero_salida_id']);
            $table->dropForeign(['paradero_llegada_id']);
            $table->dropColumn(['paradero_salida_id', 'paradero_llegada_id']);
        });
    }
};
