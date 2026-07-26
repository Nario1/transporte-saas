<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Limpiar caché de permisos Spatie
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. Definir Permisos Globales del SaaS
        $permissions = [
            // Panel admin
            'ver dashboard',

            // Maestros
            'ver vehiculos',
            'ver conductores',
            'ver propietarios',
            'ver rutas',

            // Operación diaria
            'ver vueltas',
            'ver tributos',
            'ver sanciones',

            // Reportes
            'ver reportes',

            // Sistema
            'gestionar usuarios',
            'gestionar roles',
            'gestionar empresas',
            'gestionar ajustes de empresa',
            'gestionar backups',

            // Panel conductor (panel propio)
            'conductor.dashboard',
            'conductor.tributos',
            'conductor.vueltas',
            'conductor.sanciones',
            'conductor.perfil',
        ];

        foreach ($permissions as $perm) {
            Permission::updateOrCreate(
                ['name' => $perm, 'guard_name' => 'web']
            );
        }

        // 3. Crear Roles Globales
        $superAdminRole = Role::updateOrCreate(
            ['name' => 'SUPER_ADMIN', 'guard_name' => 'web']
        );

        $conductorRole = Role::updateOrCreate(
            ['name' => 'conductor', 'guard_name' => 'web']
        );

        // SUPER_ADMIN — todos los permisos excepto ajustes locales de empresa
        $superAdminRole->syncPermissions(
            Permission::where('name', '!=', 'gestionar ajustes de empresa')->get()
        );

        // CONDUCTOR — solo sus permisos de panel móvil
        $conductorRole->syncPermissions([
            'conductor.dashboard',
            'conductor.tributos',
            'conductor.vueltas',
            'conductor.sanciones',
            'conductor.perfil',
        ]);

        // 4. Crear Usuario Maestro (Super Admin Global)
        $superAdminUser = User::updateOrCreate(
            ['email' => 'superadmin@transjunin.com'],
            [
                'empresa_id'   => null, // Global
                'conductor_id' => null,
                'name'         => 'Super Admin',
                'password'     => Hash::make('password'),
                'activo'       => true,
            ]
        );

        $superAdminUser->syncRoles($superAdminRole);

        $this->command->info('=========================================');
        $this->command->info('✅ BASE DE DATOS INICIALIZADA CORRECTAMENTE');
        $this->command->info('=========================================');
        $this->command->info('🔑 Super Admin Email: superadmin@transjunin.com');
        $this->command->info('🔑 Password: password');
        $this->command->info('-----------------------------------------');
        $this->command->info('Permisos creados: ' . count($permissions));
        $this->command->info('Roles creados: SUPER_ADMIN, conductor');
        $this->command->info('=========================================');
    }
}