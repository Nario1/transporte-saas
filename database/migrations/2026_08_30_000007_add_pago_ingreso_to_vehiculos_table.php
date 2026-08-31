<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehiculos', function (Blueprint $table) {
            $table->decimal('monto_inicial', 10, 2)->default(0.00)->after('estado');
            $table->decimal('cuota_1', 10, 2)->default(0.00)->after('monto_inicial');
            $table->decimal('cuota_2', 10, 2)->default(0.00)->after('cuota_1');
            $table->decimal('cuota_3', 10, 2)->default(0.00)->after('cuota_2');
        });
    }

    public function down(): void
    {
        Schema::table('vehiculos', function (Blueprint $table) {
            $table->dropColumn(['monto_inicial', 'cuota_1', 'cuota_2', 'cuota_3']);
        });
    }
};
