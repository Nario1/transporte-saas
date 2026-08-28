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
            $table->renameColumn('tarjeta_circulacion_tipo', 'carnet_habilitacion_tipo');
            $table->renameColumn('tarjeta_circulacion_vence', 'carnet_habilitacion_vence');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('conductores', function (Blueprint $table) {
            $table->renameColumn('carnet_habilitacion_tipo', 'tarjeta_circulacion_tipo');
            $table->renameColumn('carnet_habilitacion_vence', 'tarjeta_circulacion_vence');
        });
    }
};
