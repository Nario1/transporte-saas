<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AjusteController extends Controller
{
    /**
     * Muestra la vista principal de ajustes (puede ser resumen o redireccionar a edición).
     */
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $empresa = $user->empresa;

        // Si prefieres que el index sea directamente el formulario de edición:
        return view('admin.ajustes.index', compact('empresa'));
    }

    /**
     * Muestra el formulario para editar ajustes de la empresa.
     */
    public function edit()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $empresa = $user->empresa;
        $admin = $user;

        return view('admin.ajustes.edit', compact('empresa', 'admin'));
    }

    /**
     * Actualiza los datos de la empresa en la base de datos.
     */
    public function update(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $empresa = $user->empresa;

        $validated = $request->validate([
            'nombre'         => 'required|string|max:120',
            'ruc'            => 'required|string|max:11|unique:empresas,ruc,' . $empresa->id,
            'razon_social'   => 'nullable|string|max:160',
            'telefono'       => 'nullable|string|max:15',
            'direccion'      => 'nullable|string|max:255',
            'tributo_diario' => 'required|numeric|min:0',
            'logo'           => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            
            // Validaciones del Administrador
            'admin_name'     => 'required|string|max:255',
            'admin_email'    => 'required|email',
            'admin_password' => 'nullable|string|min:6|confirmed',
        ]);

        if ($request->hasFile('logo')) {
            // Eliminar logo anterior si existe
            if ($empresa->logo_path) {
                Storage::disk('public')->delete($empresa->logo_path);
            }
            $path = $request->file('logo')->store('empresas/logos', 'public');
            $validated['logo_path'] = $path;
        }

        $oldTributo = (float) $empresa->tributo_diario;
        // Filtrar datos exclusivos de la empresa para actualizar
        $empresaData = collect($validated)->except(['admin_name', 'admin_email', 'admin_password', 'admin_password_confirmation'])->toArray();
        $empresa->update($empresaData);
        $newTributo = (float) $empresa->tributo_diario;

        // Actualizar datos del usuario Administrador logueado
        $adminData = [];
        if ($request->filled('admin_name')) {
            $adminData['name'] = $request->admin_name;
        }
        if ($request->filled('admin_email')) {
            if ($request->admin_email !== $user->email) {
                $request->validate([
                    'admin_email' => 'unique:users,email,' . $user->id,
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
            $user->update($adminData);
        }

        $msg = 'Ajustes de empresa actualizados correctamente.';

        if ($oldTributo != $newTributo) {
            if ($oldTributo <= 0 && $newTributo > 0) {
                // Es la primera vez que se configura: Aplicar hoy mismo a flota activa
                $vehiculos = $empresa->vehiculos()->where('estado', 'activo')->get();
                foreach ($vehiculos as $v) {
                    \App\Models\Tributo::updateOrCreate(
                        ['vehiculo_id' => $v->id, 'fecha' => today()->toDateString()],
                        [
                            'empresa_id'   => $empresa->id,
                            'conductor_id' => $v->conductor_id,
                            'monto'        => $newTributo,
                            'estado'       => 'pendiente',
                        ]
                    );
                }
                $msg = "Configuración inicial completada. Terminaste de configurar tu empresa correctamente.";
            } else {
                // Es una modificación de un monto ya existente: Aplicar mañana
                $msg = "Ajustes actualizados. El cambio de monto de S/ " . number_format($oldTributo, 2) . " a S/ " . number_format($newTributo, 2) . " se verá reflejado el día de mañana.";
            }
        }

        return redirect()->route('ajustes.index')->with('success', $msg);
    }
}
