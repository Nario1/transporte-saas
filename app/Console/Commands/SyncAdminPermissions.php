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
        // Asegurarnos de que el permiso existe
        $permisoAjustes = Permission::firstOrCreate([
            'name' => 'gestionar ajustes de empresa',
            'guard_name' => 'web'
        ]);

        $rolesAdmin = Role::where('name', 'LIKE', '%_ADMIN')->get();
        $count = 0;

        foreach ($rolesAdmin as $role) {
            if (!$role->hasPermissionTo('gestionar ajustes de empresa')) {
                $role->givePermissionTo($permisoAjustes);
                $this->info("Permiso agregado al rol: {$role->name}");
                $count++;
            }
        }

        $this->info("Se actualizaron {$count} roles ADMIN.");
        return self::SUCCESS;
    }
}
