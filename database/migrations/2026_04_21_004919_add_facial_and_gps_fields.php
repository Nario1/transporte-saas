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
            $table->boolean('requiere_facial')->default(true)->after('primer_ingreso');
        });

        Schema::table('vueltas', function (Blueprint $table) {
            $table->decimal('latitud_fin', 10, 7)->nullable()->after('longitud');
            $table->decimal('longitud_fin', 10, 7)->nullable()->after('latitud_fin');
        });
    }

    public function down(): void
    {
        Schema::table('conductores', function (Blueprint $table) {
            $table->dropColumn('requiere_facial');
        });

        Schema::table('vueltas', function (Blueprint $table) {
            $table->dropColumn(['latitud_fin', 'longitud_fin']);
        });
    }
};
