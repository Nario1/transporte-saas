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
        Schema::table('conductores', function (Blueprint $table) {
            $table->string('tarjeta_circulacion_tipo', 50)->nullable()->after('licencia_vence');
            $table->date('tarjeta_circulacion_vence')->nullable()->after('tarjeta_circulacion_tipo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('conductores', function (Blueprint $table) {
            $table->dropColumn(['tarjeta_circulacion_tipo', 'tarjeta_circulacion_vence']);
        });
    }
};
