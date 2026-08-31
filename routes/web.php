<?php
// ══════════════════════════════════════════════════════════════════
// routes/web.php — TransJunín SaaS v2.0
// Incluye: Pagos MP, Reconocimiento Facial, Vueltas Automáticas
// ══════════════════════════════════════════════════════════════════

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\EmpresaController;
use App\Http\Controllers\PropietarioController;
use App\Http\Controllers\ConductorController;
use App\Http\Controllers\VehiculoController;
use App\Http\Controllers\RutaController;
use App\Http\Controllers\VueltaController;
use App\Http\Controllers\TributoController;
use App\Http\Controllers\SancionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\PagoMpController;
use App\Http\Controllers\Admin\ConductorAccesoController;
use App\Http\Controllers\Admin\RostroController;
use App\Http\Controllers\Admin\VueltasEnVivoController;
use App\Http\Controllers\Admin\ParaderoCheckinController;
use App\Http\Controllers\Conductor\DashboardController as ConductorDashboard;
use App\Http\Controllers\Conductor\PerfilController as ConductorPerfil;
use App\Http\Controllers\Conductor\TributoController as ConductorTributo;
use App\Http\Controllers\Conductor\VueltaController as ConductorVuelta;
use App\Http\Controllers\Conductor\SancionController as ConductorSancion;
use App\Http\Controllers\Conductor\VueltaAutoController;

// ── Redirección raíz ──────────────────────────────────────────────
Route::get('/', fn() => redirect()->route('login'));

// ── Autenticación ─────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login',     [LoginController::class,    'index'])->name('login');
    Route::post('/login',    [LoginController::class,    'store']);
    Route::get('/register',  [RegisterController::class, 'index'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});

// ═══════════════════════════════════════════════════════════════════
// MÓDULO 1 — Pagos Mercado Pago (RUTAS PÚBLICAS — sin auth)
// ═══════════════════════════════════════════════════════════════════
Route::get('/pagar/{token}/{tipo?}', [PagoMpController::class, 'mostrarPago'])->name('pago.public');
Route::get('/pago/consultar', [PagoMpController::class, 'consultarEstado'])->name('pago.consultar');
Route::get('/pago/retorno',         [PagoMpController::class, 'retorno'])->name('pago.retorno');
Route::post('/pago/procesar',       [PagoMpController::class, 'procesar'])->name('pago.procesar');
Route::post('/webhook/mercadopago', [PagoMpController::class, 'webhook'])->name('webhook.mercadopago');

// ── Panel Conductor ───────────────────────────────────────────────
Route::middleware(['auth', 'empresa.activa', 'role:conductor', 'forzar.password'])
    ->prefix('conductor')
    ->name('conductor.')
    ->group(function () {
        Route::get('/dashboard', [ConductorDashboard::class, 'index'])->name('dashboard');
        Route::get('/perfil',    [ConductorPerfil::class,    'index'])->name('perfil');
        Route::put('/perfil',    [ConductorPerfil::class,    'update'])->name('perfil.update');

        Route::get('/cambiar-password',  [ConductorPerfil::class, 'cambiarPassword'])
            ->name('cambiar-password')
            ->withoutMiddleware('forzar.password');
        Route::post('/cambiar-password', [ConductorPerfil::class, 'guardarPassword'])
            ->name('cambiar-password.store')
            ->withoutMiddleware('forzar.password');

        Route::get('/tributos',  [ConductorTributo::class, 'index'])->name('tributos');
        Route::get('/vueltas',   [ConductorVuelta::class,  'index'])->name('vueltas');
        Route::get('/sanciones', [ConductorSancion::class, 'index'])->name('sanciones');

        Route::post('/tributos/{tributo}/pagar-mp', [PagoMpController::class, 'generarLink'])
            ->name('tributos.pagar-mp');

        Route::post('/sanciones/{sancion}/pagar-mp', [PagoMpController::class, 'generarLinkSancion'])
            ->name('sanciones.pagar-mp');

        // Registro de Rostro (Autogestión)
        Route::get('/rostro',  [\App\Http\Controllers\Admin\RostroController::class, 'showConductor'])->name('rostro.index');
        Route::post('/rostro', [\App\Http\Controllers\Admin\RostroController::class, 'storeConductor'])->name('rostro.store');

        // MÓDULO 3 — Vueltas Automáticas
        Route::get('/vuelta/iniciar',   [VueltaAutoController::class, 'iniciarVista'])->name('vuelta.iniciar');
        Route::post('/vuelta/iniciar',  [VueltaAutoController::class, 'iniciar'])->name('vuelta.iniciar.post');
        Route::get('/vuelta/activa',    [VueltaAutoController::class, 'activaVista'])->name('vuelta.activa');
        Route::post('/vuelta/terminar', [VueltaAutoController::class, 'terminar'])->name('vuelta.terminar');
        Route::post('/vuelta/actualizar-ubicacion', [VueltaAutoController::class, 'actualizarUbicacion'])->name('vuelta.ubicacion');
        Route::get('/vuelta/estado',    [VueltaAutoController::class, 'estado'])->name('vuelta.estado');

        // Alertas de Operativos
        Route::post('/operativos/reportar', [\App\Http\Controllers\Conductor\AlertaOperativoController::class, 'store'])->name('operativos.reportar');
        Route::post('/operativos/{alerta}/finalizar', [\App\Http\Controllers\Conductor\AlertaOperativoController::class, 'finalizar'])->name('operativos.finalizar');
        Route::get('/operativos/activos', [\App\Http\Controllers\Conductor\AlertaOperativoController::class, 'getActivosApi'])->name('operativos.activos.api');
    });

// ── Panel Super Admin ─────────────────────────────────────────────
Route::middleware(['auth', 'empresa.activa', 'role:SUPER_ADMIN'])
    ->prefix('superadmin')
    ->name('superadmin.')
    ->group(function () {
        Route::get('/dashboard',               [EmpresaController::class, 'dashboard'])->name('dashboard');
        Route::get('/empresas',                [EmpresaController::class, 'index'])->name('empresas.index');
        Route::get('/empresas/create',         [EmpresaController::class, 'create'])->name('empresas.create');
        Route::post('/empresas',               [EmpresaController::class, 'store'])->name('empresas.store');
        Route::get('/empresas/{empresa}/edit', [EmpresaController::class, 'edit'])->name('empresas.edit');
        Route::put('/empresas/{empresa}',      [EmpresaController::class, 'update'])->name('empresas.update');
        Route::patch('/empresas/{empresa}/toggle', [EmpresaController::class, 'toggleStatus'])->name('empresas.toggle');
        Route::delete('/empresas/{empresa}',   [EmpresaController::class, 'destroy'])->name('empresas.destroy');

        // Auditoría Global
        Route::get('/auditoria', [App\Http\Controllers\Admin\AuditoriaController::class, 'index'])->name('auditoria.index');
        Route::get('/auditoria/{audit}', [App\Http\Controllers\Admin\AuditoriaController::class, 'show'])->name('auditoria.show');

        // Backups Globales
        Route::get('/backups', [App\Http\Controllers\Admin\BackupController::class, 'index'])->name('backups.index');
        Route::post('/backups', [App\Http\Controllers\Admin\BackupController::class, 'store'])->name('backups.store');
        Route::get('/backups/{backup}/download', [App\Http\Controllers\Admin\BackupController::class, 'download'])->name('backups.download');
        Route::delete('/backups/{backup}', [App\Http\Controllers\Admin\BackupController::class, 'destroy'])->name('backups.destroy');

        // ── Permisos Globales (NUEVO) ──────────────────────────────────────────────
        Route::get('/permisos', [App\Http\Controllers\PermisoController::class, 'index'])->name('permisos.index');
        Route::get('/permisos/crear', [App\Http\Controllers\PermisoController::class, 'create'])->name('permisos.create');
        Route::post('/permisos', [App\Http\Controllers\PermisoController::class, 'store'])->name('permisos.store');
        Route::get('/permisos/{permiso}', [App\Http\Controllers\PermisoController::class, 'show'])->name('permisos.show');
        Route::get('/permisos/{permiso}/edit', [App\Http\Controllers\PermisoController::class, 'edit'])->name('permisos.edit');
        Route::put('/permisos/{permiso}', [App\Http\Controllers\PermisoController::class, 'update'])->name('permisos.update');
        Route::delete('/permisos/{permiso}', [App\Http\Controllers\PermisoController::class, 'destroy'])->name('permisos.destroy');
    });

// ── Panel Admin ───────────────────────────────────────────────────
Route::middleware(['auth', 'empresa.activa', 'admin.configurado'])
    ->prefix('admin')
    ->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard')
        ->middleware('permission:ver dashboard');

    Route::middleware('permission:gestionar usuarios')->group(function () {
        Route::get('/users',             [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create',      [UserController::class, 'create'])->name('users.create');
        Route::post('/users',            [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}',      [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}',   [UserController::class, 'destroy'])->name('users.destroy');
    });

    Route::middleware('permission:gestionar roles')->group(function () {
        Route::get('/roles',             [RoleController::class, 'index'])->name('roles.index');
        Route::get('/roles/create',      [RoleController::class, 'create'])->name('roles.create');
        Route::post('/roles',            [RoleController::class, 'store'])->name('roles.store');
        Route::get('/roles/{role}',      [RoleController::class, 'show'])->name('roles.show');
        Route::get('/roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit');
        Route::put('/roles/{role}',      [RoleController::class, 'update'])->name('roles.update');
        Route::delete('/roles/{role}',   [RoleController::class, 'destroy'])->name('roles.destroy');
    });

    Route::middleware('permission:ver propietarios')->group(function () {
        Route::get('/propietarios',                    [PropietarioController::class, 'index'])->name('propietarios.index');
        Route::get('/propietarios/create',             [PropietarioController::class, 'create'])->name('propietarios.create');
        Route::post('/propietarios',                   [PropietarioController::class, 'store'])->name('propietarios.store');
        Route::get('/propietarios/{propietario}/edit', [PropietarioController::class, 'edit'])->name('propietarios.edit');
        Route::get('/propietarios/{propietario}',      [PropietarioController::class, 'show'])->name('propietarios.show');
        Route::put('/propietarios/{propietario}',      [PropietarioController::class, 'update'])->name('propietarios.update');
        Route::delete('/propietarios/{propietario}',   [PropietarioController::class, 'destroy'])->name('propietarios.destroy');
    });

    Route::middleware('permission:ver vehiculos')->group(function () {
        Route::get('/vehiculos',                 [VehiculoController::class, 'index'])->name('vehiculos.index');
        Route::get('/vehiculos/create',          [VehiculoController::class, 'create'])->name('vehiculos.create');
        Route::post('/vehiculos',                [VehiculoController::class, 'store'])->name('vehiculos.store');
        Route::get('/vehiculos/{vehiculo}',      [VehiculoController::class, 'show'])->name('vehiculos.show');
        Route::get('/vehiculos/{vehiculo}/edit', [VehiculoController::class, 'edit'])->name('vehiculos.edit');
        Route::put('/vehiculos/{vehiculo}',      [VehiculoController::class, 'update'])->name('vehiculos.update');
        Route::delete('/vehiculos/{vehiculo}',   [VehiculoController::class, 'destroy'])->name('vehiculos.destroy');
    });

    Route::middleware('permission:ver conductores')->group(function () {
        Route::get('/conductores',                   [ConductorController::class, 'index'])->name('conductores.index');
        Route::get('/conductores/create',            [ConductorController::class, 'create'])->name('conductores.create');
        Route::post('/conductores',                  [ConductorController::class, 'store'])->name('conductores.store');
        Route::get('/conductores/{conductor}',       [ConductorController::class, 'show'])->name('conductores.show');
        Route::get('/conductores/{conductor}/edit',  [ConductorController::class, 'edit'])->name('conductores.edit');
        Route::put('/conductores/{conductor}',       [ConductorController::class, 'update'])->name('conductores.update');
        Route::delete('/conductores/{conductor}',    [ConductorController::class, 'destroy'])->name('conductores.destroy');
        Route::post('conductores/{conductor}/acceso/toggle', [ConductorAccesoController::class, 'toggle'])->name('conductores.acceso.toggle');
        Route::post('/conductores/{conductor}/acceso',         [ConductorAccesoController::class, 'store'])->name('conductores.acceso.store');
        Route::delete('/conductores/{conductor}/acceso',       [ConductorAccesoController::class, 'destroy'])->name('conductores.acceso.destroy');
        Route::post('/conductores/{conductor}/reset-password', [ConductorAccesoController::class, 'resetPassword'])->name('conductores.acceso.reset');
        Route::post('/conductores/{conductor}/toggle-facial', [ConductorController::class, 'toggleFacial'])->name('conductores.toggle-facial');

        // MÓDULO 2 — Reconocimiento Facial
        Route::get('/conductores/{conductor}/rostro',    [RostroController::class, 'show'])->name('conductores.rostro.show');
        Route::post('/conductores/{conductor}/rostro',   [RostroController::class, 'store'])->name('conductores.rostro.store');
        Route::delete('/conductores/{conductor}/rostro', [RostroController::class, 'destroy'])->name('conductores.rostro.destroy');
    });

    Route::middleware('permission:ver rutas')->group(function () {
        Route::get('/rutas',             [RutaController::class, 'index'])->name('rutas.index');
        Route::get('/rutas/create',      [RutaController::class, 'create'])->name('rutas.create');
        Route::post('/rutas',            [RutaController::class, 'store'])->name('rutas.store');
        Route::get('/rutas/{ruta}',      [RutaController::class, 'show'])->name('rutas.show');
        Route::get('/rutas/{ruta}/edit', [RutaController::class, 'edit'])->name('rutas.edit');
        Route::put('/rutas/{ruta}',      [RutaController::class, 'update'])->name('rutas.update');
        Route::delete('/rutas/{ruta}',   [RutaController::class, 'destroy'])->name('rutas.destroy');
        Route::post('/rutas/{ruta}/paraderos',              [RutaController::class, 'storeParadero'])->name('rutas.paraderos.store');
        Route::delete('/rutas/{ruta}/paraderos/{paradero}', [RutaController::class, 'destroyParadero'])->name('rutas.paraderos.destroy');
        Route::put('/rutas/{ruta}/paraderos/{paradero}/coordenadas', [RutaController::class, 'updateParaderoCoordenadas'])->name('rutas.paraderos.coordenadas.update');

        // Kiosco de control facial en paradero
        Route::get('/rutas/{ruta}/paraderos/{paradero}/kiosco', [ParaderoCheckinController::class, 'index'])->name('rutas.kiosco');
        Route::get('/api/paraderos/conductores-rostros', [ParaderoCheckinController::class, 'getConductoresRostros'])->name('paraderos.api.rostros');
        Route::post('/paraderos/{paradero}/checkin', [ParaderoCheckinController::class, 'store'])->name('paraderos.checkin');
    });

    Route::middleware('permission:ver vueltas')->group(function () {
        Route::get('/vueltas',             [VueltaController::class, 'index'])->name('vueltas.index');
        Route::get('/vueltas/create',      [VueltaController::class, 'create'])->name('vueltas.create');
        Route::post('/vueltas',            [VueltaController::class, 'store'])->name('vueltas.store');
        Route::post('/vueltas/{vuelta}/completar', [VueltaController::class, 'completar'])->name('vueltas.completar');
        Route::delete('/vueltas/{vuelta}', [VueltaController::class, 'destroy'])->name('vueltas.destroy');

        // MÓDULO 3 — Dashboard Vueltas en Tiempo Real
        Route::get('/vueltas/en-vivo',          [VueltasEnVivoController::class, 'index'])->name('vueltas.en-vivo');
        Route::get('/api/vueltas-activas', [VueltasEnVivoController::class, 'activas'])->name('vueltas.api.activas');
    });

    Route::middleware('permission:gestionar alertas')->group(function () {
        Route::get('/alertas', [\App\Http\Controllers\Conductor\AlertaOperativoController::class, 'adminIndex'])->name('admin.alertas.index');
        Route::post('/alertas', [\App\Http\Controllers\Conductor\AlertaOperativoController::class, 'adminStore'])->name('admin.alertas.store');
        Route::post('/alertas/{alerta}/finalizar', [\App\Http\Controllers\Conductor\AlertaOperativoController::class, 'adminFinalizar'])->name('admin.alertas.finalizar');
        
        Route::post('/puntos-control', [\App\Http\Controllers\Conductor\AlertaOperativoController::class, 'adminAddPunto'])->name('admin.puntos.store');
        Route::delete('/puntos-control/{punto}', [\App\Http\Controllers\Conductor\AlertaOperativoController::class, 'adminDeletePunto'])->name('admin.puntos.destroy');
    });

    Route::middleware('permission:ver tributos')->group(function () {
        Route::get('/tributos',                   [TributoController::class, 'index'])->name('tributos.index');
        Route::get('/tributos/create',            [TributoController::class, 'create'])->name('tributos.create');
        Route::post('/tributos',                  [TributoController::class, 'store'])->name('tributos.store');
        Route::post('/tributos/{tributo}/cobrar', [TributoController::class, 'cobrar'])->name('tributos.cobrar');
        Route::post('/tributos/{tributo}/exonerar', [TributoController::class, 'exonerar'])->name('tributos.exonerar');
        Route::post('/tributos/exonerar-todo',    [TributoController::class, 'exonerarTodoHoy'])->name('tributos.exonerar-todo');
        Route::post('/tributos/generar-dia',      [TributoController::class, 'generarDelDia'])->name('tributos.generar');
        Route::get('/tributos/vehiculo/{vehiculo}/detalle', [TributoController::class, 'detalleDeuda'])->name('tributos.detalle');
    });

    Route::middleware('permission:ver sanciones')->group(function () {
        Route::get('/sanciones',                  [SancionController::class, 'index'])->name('sanciones.index');
        Route::get('/sanciones/create',           [SancionController::class, 'create'])->name('sanciones.create');
        Route::post('/sanciones',                 [SancionController::class, 'store'])->name('sanciones.store');
        Route::post('/sanciones/{sancion}/pagar', [SancionController::class, 'pagar'])->name('sanciones.pagar');
        Route::post('/sanciones/{sancion}/exonerar', [SancionController::class, 'exonerar'])->name('sanciones.exonerar');
        Route::delete('/sanciones/{sancion}',     [SancionController::class, 'destroy'])->name('sanciones.destroy');
    });

    Route::middleware('permission:ver reportes')
        ->prefix('reportes')
        ->name('reportes.')
        ->group(function () {
            Route::get('/',           [ReporteController::class, 'index'])->name('index');
            Route::get('/tributos',   [ReporteController::class, 'tributos'])->name('tributos');
            Route::get('/vueltas',    [ReporteController::class, 'vueltas'])->name('vueltas');
            Route::get('/sanciones',  [ReporteController::class, 'sanciones'])->name('sanciones');
            Route::get('/documentos', [ReporteController::class, 'documentos'])->name('documentos');
            Route::get('/deudas',     [ReporteController::class, 'deudas'])->name('deudas');
        });

    Route::middleware('permission:gestionar ajustes de empresa')->group(function () {
        Route::get('/ajustes',                 [\App\Http\Controllers\Admin\AjusteController::class, 'index'])->name('ajustes.index');
        Route::get('/ajustes/edit',            [\App\Http\Controllers\Admin\AjusteController::class, 'edit'])->name('ajustes.edit');
        Route::put('/ajustes',                 [\App\Http\Controllers\Admin\AjusteController::class, 'update'])->name('ajustes.update');
    });

});

Route::get('/debug-roles', function() {
    $user = auth()->user();
    if (!$user) return 'No estás logueado en la aplicación.';
    
    $roles = $user->roles->pluck('name')->toArray();
    $permissionsDirect = $user->permissions->pluck('name')->toArray();
    $permissionsViaRoles = $user->getPermissionsViaRoles()->pluck('name')->toArray();
    $allPermissions = $user->getAllPermissions()->pluck('name')->toArray();
    
    $permExists = \Spatie\Permission\Models\Permission::where('name', 'gestionar alertas')->exists();
    
    $rolesAdminInDb = \Spatie\Permission\Models\Role::where('name', 'LIKE', '%_ADMIN')->get()->map(function($r) {
        try {
            $hasPerm = $r->hasPermissionTo('gestionar alertas');
        } catch (\Exception $e) {
            $hasPerm = false;
        }
        return [
            'role' => $r->name,
            'tiene_gestionar_alertas' => $hasPerm
        ];
    })->toArray();

    return response()->json([
        'usuario' => $user->email,
        'roles_del_usuario' => $roles,
        'permiso_gestionar_alertas_existe_en_bd' => $permExists,
        'tiene_permiso_gestionar_alertas_directo' => $user->hasDirectPermission('gestionar alertas') ? true : false,
        'tiene_permiso_gestionar_alertas_via_rol' => $user->hasPermissionTo('gestionar alertas') ? true : false,
        'permisos_directos' => $permissionsDirect,
        'permisos_via_roles' => $permissionsViaRoles,
        'todos_los_permisos' => $allPermissions,
        'roles_admin_en_bd_y_estado_permiso' => $rolesAdminInDb
    ]);
});
