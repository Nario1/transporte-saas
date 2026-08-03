<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Http\Requests\StoreEmpresaRequest;
use App\Http\Requests\UpdateEmpresaRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EmpresaController extends Controller
{
    /**
     * Dashboard Maestro para German (SUPER_ADMIN)
     */
    public function dashboard()
    {
        return view('superadmin.index');
    }

    /**
     * Listado de todas las empresas.
     */
    public function index()
    {
        $empresas = Empresa::orderBy('created_at', 'desc')->get();
        return view('superadmin.empresas.index', compact('empresas'));
    }

    /**
     * Formulario para crear empresa.
     */
    public function create()
    {
        return view('superadmin.empresas.create');
    }

    /**
     * Guardar nueva empresa en la base de datos.
     */
    public function store(StoreEmpresaRequest $request)
    {
        $data = $request->validated();

        // Validar datos del administrador
        $request->validate([
            'admin_name'  => 'required|string|max:255',
            'admin_email' => 'required|email|unique:users,email',
            'password'    => 'required|string|min:6|confirmed',
        ]);

        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')->store('logos', 'public');
        }

        // Filtrar datos exclusivos de la empresa para su creación
        $empresaData = collect($data)->except(['admin_name', 'admin_email', 'password', 'password_confirmation'])->toArray();
        $empresa = Empresa::create($empresaData);

        // Crear los roles correspondientes para la nueva empresa
        $prefijo = 'e' . $empresa->id . '_';
        
        $adminRole = \Spatie\Permission\Models\Role::updateOrCreate(
            ['name' => $prefijo . 'ADMIN', 'guard_name' => 'web']
        );

        \Spatie\Permission\Models\Role::updateOrCreate(
            ['name' => $prefijo . 'OPERADOR', 'guard_name' => 'web']
        );

        // Asignar permisos al rol de Administrador de esta empresa
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

        // Crear el usuario administrador asociado a la empresa
        $user = \App\Models\User::create([
            'empresa_id'   => $empresa->id,
            'name'         => $request->admin_name,
            'email'        => $request->admin_email,
            'password'     => \Illuminate\Support\Facades\Hash::make($request->password),
            'activo'       => true,
        ]);

        $user->syncRoles([$adminRole]);

        return redirect()->route('superadmin.empresas.index')
            ->with('success', 'Empresa y su usuario Administrador registrados correctamente.');
    }

    /**
     * Formulario de edición.
     */
    public function edit(Empresa $empresa)
    {
        $prefijo = 'e' . $empresa->id . '_';
        $admin = \App\Models\User::role($prefijo . 'ADMIN')->first();

        return view('superadmin.empresas.edit', compact('empresa', 'admin'));
    }

    /**
     * Actualizar los datos de la empresa.
     */
    public function update(UpdateEmpresaRequest $request, Empresa $empresa)
    {
        $data = $request->validated();

        if ($request->hasFile('logo')) {
            // Borrar logo anterior si existe
            if ($empresa->logo_path) {
                Storage::disk('public')->delete($empresa->logo_path);
            }
            $data['logo_path'] = $request->file('logo')->store('logos', 'public');
        }

        // Convertir 'activa' a boolean si está presente
        if (isset($data['activa'])) {
            $data['activa'] = (bool) $data['activa'];
        }

        $empresa->update($data);

        // Actualizar el administrador principal de la empresa
        $request->validate([
            'admin_name'     => 'nullable|string|max:255',
            'admin_email'    => 'nullable|email',
            'admin_password' => 'nullable|string|min:6|confirmed',
        ]);

        $prefijo = 'e' . $empresa->id . '_';
        $admin = \App\Models\User::role($prefijo . 'ADMIN')->first();

        if ($admin) {
            $adminData = [];
            if ($request->filled('admin_name')) {
                $adminData['name'] = $request->admin_name;
            }
            if ($request->filled('admin_email')) {
                // Verificar si el email cambió y no está en uso por otro
                if ($request->admin_email !== $admin->email) {
                    $request->validate([
                        'admin_email' => 'unique:users,email,' . $admin->id,
                    ], [
                        'admin_email.unique' => 'El correo electrónico ya está registrado en el sistema.',
                    ]);
                    $adminData['email'] = $request->admin_email;
                }
            }
            if ($request->filled('admin_password')) {
                $adminData['password'] = \Illuminate\Support\Facades\Hash::make($request->admin_password);
            }
            
            if (!empty($adminData)) {
                $admin->update($adminData);
            }
        }

        return redirect()->route('superadmin.empresas.index')
            ->with('success', 'Información de la empresa y su administrador actualizada.');
    }

    /**
     * Interruptor de encendido/apagado del servicio (activa).
     */
    public function toggleStatus(Empresa $empresa)
    {
        $empresa->update([
            'activa' => !$empresa->activa
        ]);

        $status = $empresa->activa ? 'activada (Acceso Permitido)' : 'suspendida (Acceso Denegado)';
        
        return back()->with('success', "La empresa {$empresa->nombre} ha sido {$status}.");
    }

    /**
     * Eliminación (Soft Delete si lo tienes configurado en el modelo).
     */
    public function destroy(Empresa $empresa)
    {
        // Validar si tiene usuarios antes de borrar (opcional)
        if ($empresa->users()->count() > 0) {
            return back()->with('error', 'No se puede eliminar una empresa con usuarios activos.');
        }

        $empresa->delete();

        return redirect()->route('superadmin.empresas.index')
            ->with('success', 'La empresa ha sido eliminada del sistema.');
    }
}