<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\Empresa;
use App\Models\User;
use App\Models\Conductor;
use App\Models\Propietario;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // 1. CREAR EMPRESA DEMO ───────────────────────────────────────────
        $empresa = Empresa::updateOrCreate(
            ['ruc' => '20123456789'],
            [
                'nombre'           => 'Transportes Junín S.A.',
                'razon_social'     => 'Transportes Junín Sociedad Anónima',
                'telefono'         => '064-123456',
                'direccion'        => 'Av. Giráldez 123, Huancayo',
                'plan'             => 'enterprise',
                'activa'           => true,
                'tributo_diario'   => 24.00,
            ]
        );

        $this->command->info("✅ Empresa creada: {$empresa->nombre} (ID: {$empresa->id})");

        // 2. CREAR ROLES PARA ESTA EMPRESA ────────────────────────────────
        $prefijo = 'e' . $empresa->id . '_';

        $adminRole = Role::updateOrCreate(
            
            ['name' => $prefijo . 'ADMIN', 'guard_name' => 'web']
        );

        $operadorRole = Role::updateOrCreate(
            ['name' => $prefijo . 'OPERADOR', 'guard_name' => 'web']
        );

        // Permisos para el ADMIN de la empresa
        $adminRole->syncPermissions([
            'ver dashboard',
            'ver vehiculos',
            'ver conductores',
            'ver propietarios',
            'ver rutas',
            'ver vueltas',
            'ver tributos',
            'ver sanciones',
            'ver reportes',
            'gestionar usuarios',
            'gestionar roles',
            'gestionar ajustes de empresa',
            'gestionar backups',
        ]);

        // Permisos para el OPERADOR (solo operativo)
        $operadorRole->syncPermissions([
            'ver vehiculos',
            'ver conductores',
            'ver vueltas',
            'ver tributos',
            'ver sanciones',
            'ver reportes',
        ]);

        // 3. USUARIO ADMINISTRADOR DE LA EMPRESA ─────────────────────────
        $adminUser = User::updateOrCreate(
            ['email' => 'admin@transjunin.com'],
            [
                'empresa_id'   => $empresa->id,
                'name'         => 'Admin TransJunín',
                'password'     => Hash::make('admin123'),
                'activo'       => true,
            ]
        );
        $adminUser->syncRoles([$adminRole]);

        $this->command->info("✅ Administrador de empresa creado: admin@transjunin.com / admin123");

        // 4. USUARIO OPERADOR ──────────────────────────────────────────────
        User::updateOrCreate(
            ['email' => 'operador@transjunin.com'],
            [
                'empresa_id'   => $empresa->id,
                'name'         => 'Operador Demo',
                'password'     => Hash::make('operador123'),
                'activo'       => true,
            ]
        )->syncRoles([$operadorRole]);

        // 5. DATOS MAESTROS (PROPIETARIO Y CONDUCTOR) ────────────────────
        $propietario = Propietario::updateOrCreate(
            ['dni' => '12345678'],
            [
                'empresa_id' => $empresa->id,
                'nombre'     => 'Juan',
                'apellidos'  => 'Pérez López',
                'telefono'   => '999111222',
                'email'      => 'juan.perez@email.com',
                'direccion'  => 'Jr. Lima 456, Huancayo',
            ]
        );

        $conductor = Conductor::updateOrCreate(
            ['dni' => '87654321'],
            [
                'empresa_id'     => $empresa->id,
                'propietario_id' => $propietario->id,
                'nombre'         => 'Carlos',
                'apellidos'      => 'Quispe Huanca',
                'email'          => 'conductor@transjunin.com',
                'tipo_licencia'  => 'A-IIb',
                'licencia_vence' => now()->addYear(),
                'estado'         => 'activo',
            ]
        );

        // USUARIO CONDUCTOR
        $userConductor = User::updateOrCreate(
            ['email' => 'conductor@transjunin.com'],
            [
                'empresa_id'   => $empresa->id,
                'conductor_id' => $conductor->id,
                'name'         => 'Carlos Quispe',
                'password'     => Hash::make('conductor123'),
                'activo'       => true,
            ]
        );
        $userConductor->syncRoles('conductor');

        $this->command->info("✅ Datos de prueba (Propietario, Conductor, Usuarios) cargados correctamente.");
    }
}
