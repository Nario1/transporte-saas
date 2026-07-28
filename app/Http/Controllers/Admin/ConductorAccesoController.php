<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Conductor;
use App\Models\User;
use App\Http\Requests\StoreConductorAccesoRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ConductorAccesoController extends Controller
{
    // Crear acceso inicial
    public function store(StoreConductorAccesoRequest $request, Conductor $conductor)
    {
        $user = Auth::user();
        abort_if($conductor->empresa_id !== $user->empresa_id, 403);

        if ($conductor->user()->exists()) {
            return back()->with('error', 'Este conductor ya tiene un usuario creado.');
        }

        // Obtener la placa del vehículo asignado
        $vehiculo = $conductor->vehiculos()->first();
        if (!$vehiculo) {
            return back()->with('error', 'No se puede crear el acceso: El conductor no tiene un vehículo asignado.');
        }

        $placa = strtoupper($vehiculo->placa);

        // Verificar si la placa ya está en uso por otro usuario (incluyendo eliminados por soft-delete)
        $existingUser = User::withTrashed()->where('email', $placa)->first();
        if ($existingUser) {
            if ($existingUser->conductor_id === $conductor->id) {
                // Si el usuario eliminado pertenece al mismo conductor, lo restauramos
                $existingUser->restore();
                $existingUser->update([
                    'activo'   => true,
                    'password' => Hash::make($placa),
                ]);
                $conductor->update(['primer_ingreso' => true]);
                return back()->with('success', "Acceso restaurado. Usuario: \"{$placa}\" y la contraseña lo mismo");
            } else {
                return back()->with('error', "La placa {$placa} ya está registrada como usuario en el sistema por otro conductor.");
            }
        }

        $nuevoUser = User::create([
            'empresa_id'   => $conductor->empresa_id,
            'conductor_id' => $conductor->id,
            'name'         => $conductor->nombre . ' ' . $conductor->apellidos,
            'email'        => $placa, // Usamos la placa como identificador
            'password'     => Hash::make($placa),
            'activo'       => true,
        ]);

        $nuevoUser->assignRole('conductor');
        $conductor->update(['primer_ingreso' => true]);

        return back()->with('success', "Acceso creado Usuario: \"{$placa}\" y la contraseña lo mismo");
    }

    // NUEVO: Activar o Desactivar cuenta (Sin borrarla)
    public function toggle(Conductor $conductor)
    {
        $user = Auth::user();
        abort_if($conductor->empresa_id !== $user->empresa_id, 403);

        $usuarioConductor = $conductor->user;
        
        if (!$usuarioConductor) {
            return back()->with('error', 'No existe un usuario para este conductor.');
        }

        $usuarioConductor->update([
            'activo' => !$usuarioConductor->activo
        ]);

        $estado = $usuarioConductor->activo ? 'activado' : 'desactivado';
        return back()->with('success', "Acceso {$estado} correctamente.");
    }

    public function destroy(Conductor $conductor)
    {
        $user = Auth::user();
        abort_if($conductor->empresa_id !== $user->empresa_id, 403);

        // Buscar el usuario del conductor (incluso si ya fue soft-deletado previamente)
        $usuario = User::withTrashed()->where('conductor_id', $conductor->id)->first();
        if ($usuario) {
            $usuario->forceDelete(); // Eliminar de forma permanente para evitar conflictos de llave única
            $conductor->update(['primer_ingreso' => false]);
        }

        return back()->with('success', 'Credenciales eliminadas por completo.');
    }

    // Resetear clave
    public function resetPassword(Conductor $conductor)
    {
        $user = Auth::user();
        abort_if($conductor->empresa_id !== $user->empresa_id, 403);

        $vehiculo = $conductor->vehiculos()->first();
        if (!$vehiculo) {
            return back()->with('error', 'No se puede resetear: El conductor no tiene un vehículo asignado.');
        }

        $placa = strtoupper($vehiculo->placa);

        $conductor->user->update([
            'email'    => $placa, // Sincronizamos el usuario a la placa actual
            'password' => Hash::make($placa),
        ]);

        $conductor->update(['primer_ingreso' => true]);

        return back()->with('success', "Contraseña reiniciada correctamente. Nueva clave temporal: \"{$placa}\".");
    }
}