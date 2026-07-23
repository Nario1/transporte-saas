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
        Schema::table('sanciones', function (Blueprint $table) {
            // Actualizar el enum si la DB lo soporta (en MySQL necesitamos recrear el campo o usar string si queremos flexibilidad)
            // Como ya es enum, intentamos cambiarlo.
            $table->enum('estado', ['pendiente', 'pagado', 'exonerado'])->default('pendiente')->change();

            $table->text('motivo_exoneracion')->nullable()->after('descripcion');
            $table->timestamp('exonerado_at')->nullable()->after('cobrado_at');
            $table->foreignId('exonerado_por')
                ->nullable()
                ->after('cobrado_por')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sanciones', function (Blueprint $table) {
            $table->enum('estado', ['pendiente', 'pagado'])->default('pendiente')->change();
            $table->dropForeign(['exonerado_por']);
            $table->dropColumn(['motivo_exoneracion', 'exonerado_at', 'exonerado_por']);
        });
    }
};
