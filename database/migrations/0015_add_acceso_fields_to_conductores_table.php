<?php
 
// ══════════════════════════════════════════════════════════════════
// database/migrations/xxxx_add_acceso_fields_to_conductores_table.php
// ══════════════════════════════════════════════════════════════════
 
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
 
return new class extends Migration
{
    public function up(): void
    {
        // ── Agregar campos de acceso y documentos a conductores ──
        Schema::table('conductores', function (Blueprint $table) {
            // Documentos físicos/digitales
            $table->date('vigencia_examen_medico')->nullable()->after('licencia_vence');
            $table->string('licencia_digital')->nullable()->after('vigencia_examen_medico');  // path archivo
            $table->string('dni_digital')->nullable()->after('licencia_digital');              // path archivo
 
            // Control de primer ingreso
            $table->boolean('primer_ingreso')->default(true)->after('dni_digital');
        });
 
        // ── Agregar conductor_id a users ──
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('conductor_id')
                  ->nullable()
                  ->after('empresa_id')
                  ->constrained('conductores')
                  ->nullOnDelete();
        });
    }
 
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['conductor_id']);
            $table->dropColumn('conductor_id');
        });
 
        Schema::table('conductores', function (Blueprint $table) {
            $table->dropColumn([
                'vigencia_examen_medico',
                'licencia_digital',
                'dni_digital',
                'primer_ingreso',
            ]);
        });
    }
};