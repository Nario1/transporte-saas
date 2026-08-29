<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class SyncAdminPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-admin-permissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincroniza el permiso de gestionar ajustes a todos los roles ADMIN de las empresas existentes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Asegurarnos de que los permisos existen
        $permisoAjustes = Permission::firstOrCreate([
            'name' => 'gestionar ajustes de empresa',
            'guard_name' => 'web'
        ]);

        $permisoAlertas = Permission::firstOrCreate([
            'name' => 'gestionar alertas',
            'guard_name' => 'web'
        ]);

        $rolesAdmin = Role::where('name', 'LIKE', '%_ADMIN')->get();
        $countAjustes = 0;
        $countAlertas = 0;

        foreach ($rolesAdmin as $role) {
            if (!$role->hasPermissionTo('gestionar ajustes de empresa')) {
                $role->givePermissionTo($permisoAjustes);
                $this->info("Permiso 'gestionar ajustes de empresa' agregado al rol: {$role->name}");
                $countAjustes++;
            }
            if (!$role->hasPermissionTo('gestionar alertas')) {
                $role->givePermissionTo($permisoAlertas);
                $this->info("Permiso 'gestionar alertas' agregado al rol: {$role->name}");
                $countAlertas++;
            }
        }

        // Limpiar la caché de Spatie para que se aplique de inmediato
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->info("Se actualizaron {$countAjustes} roles ADMIN con ajustes y {$countAlertas} con alertas.");
        return self::SUCCESS;
    }
}
