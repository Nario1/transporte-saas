<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Empresa;
use App\Models\Propietario;
use App\Models\Conductor;
use App\Models\Vehiculo;
use App\Models\Ruta;
use App\Models\Tributo;
use App\Models\Vuelta;
use Illuminate\Support\Facades\DB;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Obtener la primera empresa (o crear una si no existe)
        $empresa = Empresa::first() ?? Empresa::create([
            'nombre' => 'Transporte Saas Demo',
            'ruc' => '20123456789',
            'razon_social' => 'Transporte Saas S.A.C.',
            'activa' => true,
        ]);

        $empresaId = $empresa->id;

        // ── LIMPIEZA DE DATOS PREVIOS (Evitando conflictos de FK) ──
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        Tributo::where('empresa_id', $empresaId)->delete();
        Vuelta::where('empresa_id', $empresaId)->delete();
        DB::table('vehiculo_rutas')->whereIn('ruta_id', Ruta::where('empresa_id', $empresaId)->pluck('id'))->delete();
        Vehiculo::where('empresa_id', $empresaId)->delete();
        Conductor::where('empresa_id', $empresaId)->delete();
        Propietario::where('empresa_id', $empresaId)->delete();
        Ruta::where('empresa_id', $empresaId)->delete();
        
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // ── GENERACIÓN DE DATOS SOLICITADOS (20 de cada uno, 2 Rutas) ──

        // 1. EXACTAMENTE 2 RUTAS
        $ruta1 = Ruta::create([
            'empresa_id' => $empresaId,
            'nombre' => 'Ruta Troncal Norte-Sur',
            'origen' => 'Terminal Norte',
            'destino' => 'Terminal Sur',
            'codigo' => 'R-001',
            'estado' => 'activa',
            'duracion_min' => 60
        ]);

        $ruta2 = Ruta::create([
            'empresa_id' => $empresaId,
            'nombre' => 'Ruta Circular Este-Oeste',
            'origen' => 'Paradero Este',
            'destino' => 'Paradero Oeste',
            'codigo' => 'R-002',
            'estado' => 'activa',
            'duracion_min' => 45
        ]);

        $rutas = [$ruta1->id, $ruta2->id];

        // 2. 20 PROPIETARIOS
        $propietarios = Propietario::factory()->count(20)->create([
            'empresa_id' => $empresaId,
        ]);

        // 3. 20 CONDUCTORES
        $conductores = Conductor::factory()->count(20)->create([
            'empresa_id' => $empresaId,
        ]);

        // 4. 20 VEHÍCULOS
        for ($i = 1; $i <= 20; $i++) {
            $vehiculo = Vehiculo::factory()->create([
                'empresa_id'     => $empresaId,
                'propietario_id' => $propietarios->random()->id,
                'conductor_id'   => $conductores->random()->id,
                'numero_flota'   => $i, // Números correlativos para fácil verificación
                'estado'         => 'activo'
            ]);

            // Asignar una ruta aleatoria (de las 2 que creamos)
            $rutaId = $rutas[array_rand($rutas)];
            $vehiculo->rutas()->attach($rutaId, [
                'activo' => true,
                'fecha_asignacion' => now()
            ]);

            // Crear un tributo pendiente para hoy
            Tributo::create([
                'empresa_id'   => $empresaId,
                'vehiculo_id'  => $vehiculo->id,
                'conductor_id' => $vehiculo->conductor_id,
                'monto'        => 24.00,
                'fecha'        => today(),
                'estado'       => 'pendiente'
            ]);
        }
    }
}
