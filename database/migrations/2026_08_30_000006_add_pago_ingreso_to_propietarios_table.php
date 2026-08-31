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
        Schema::table('propietarios', function (Blueprint $table) {
            $table->decimal('monto_inicial', 8, 2)->default(0.00)->after('notas');
            $table->decimal('cuota_1', 8, 2)->default(0.00)->after('monto_inicial');
            $table->decimal('cuota_2', 8, 2)->default(0.00)->after('cuota_1');
            $table->decimal('cuota_3', 8, 2)->default(0.00)->after('cuota_2');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('propietarios', function (Blueprint $table) {
            $table->dropColumn(['monto_inicial', 'cuota_1', 'cuota_2', 'cuota_3']);
        });
    }
};
