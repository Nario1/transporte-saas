<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use App\Models\Empresa;
use Illuminate\Support\Facades\Hash;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ── 1. PERMISOS GLOBALES ──────────────────────────────────
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

        // ── 2. ROLES GLOBALES DEL SISTEMA ─────────────────────────
        $superAdmin = Role::updateOrCreate(
            ['name' => 'SUPER_ADMIN', 'guard_name' => 'web']
        );

        $conductor = Role::updateOrCreate(
            ['name' => 'conductor', 'guard_name' => 'web']
        );

        // SUPER_ADMIN — todos los permisos EXCEPTO ajustes locales de empresa
        // El Super Admin usa el panel global de empresas para gestionar todo.
        $superAdmin->syncPermissions(
            Permission::where('name', '!=', 'gestionar ajustes de empresa')->get()
        );

        // CONDUCTOR — solo su panel
        $conductor->syncPermissions([
            'conductor.dashboard',
            'conductor.tributos',
            'conductor.vueltas',
            'conductor.sanciones',
            'conductor.perfil',
        ]);

        // ── 3. USUARIO MAESTRO (SUPER ADMIN GLOBAL) ─────────────
        $userMaestro = User::updateOrCreate(
            ['email' => 'superadmin@transjunin.com'],
            [
                'empresa_id'   => null, // Global, sin empresa
                'conductor_id' => null,
                'name'         => 'German Reyes (Super Admin)',
                'password'     => Hash::make('password'),
                'activo'       => true,
            ]
        );

        $userMaestro->syncRoles($superAdmin);

        // ── INFO ──────────────────────────────────────────────────
        $this->command->info('✅ Permisos creados: ' . count($permissions));
        $this->command->info('✅ Roles globales: SUPER_ADMIN, conductor');
        $this->command->info('✅ Usuario maestro global: superadmin@transjunin.com / password');
    }
}