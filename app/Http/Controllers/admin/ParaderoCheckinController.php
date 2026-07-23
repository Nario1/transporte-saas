<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Conductor;
use App\Models\ConductorRostro;
use App\Models\RutaParadero;
use App\Models\ParaderoCheckin;
use App\Models\Vuelta;
use App\Http\Requests\StoreParaderoCheckinRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ParaderoCheckinController extends Controller
{
    /**
     * Muestra la vista "Kiosco" (tablet/PC) en un paradero específico.
     * Esta vista accederá a la cámara para leer rostros entrantes.
     */
    public function index(RutaParadero $paradero)
    {
        $empresaId = auth()->user()->empresa_id;

        // Verificar pertenencia a la empresa (a través de la ruta)
        if ($paradero->ruta->empresa_id !== $empresaId) {
            abort(403, 'No autorizado');
        }

        return view('admin.paraderos.kiosco', compact('paradero'));
    }

    /**
     * API: Obtiene los embeddings de todos los conductores ACTIVOS de la empresa.
     * El Kiosco descarga esto 1 vez al cargar para hacer match offline rápido.
     */
    public function getConductoresRostros()
    {
        $empresaId = auth()->user()->empresa_id;

        $rostros = ConductorRostro::where('activo', true)
            ->whereHas('conductor', function ($q) use ($empresaId) {
                $q->where('empresa_id', $empresaId)->where('estado', 'activo');
            })
            ->with(['conductor:id,nombre,apellido,numero_documento'])
            ->get();

        return response()->json($rostros);
    }

    /**
     * POST: Recibe el ID del conductor que hizo match y registra su Vuelta/Paradero.
     */
    public function store(StoreParaderoCheckinRequest $request, RutaParadero $paradero)
    {
        $empresaId = auth()->user()->empresa_id;

        $request->validated();

        $conductor = Conductor::findOrFail($request->conductor_id);

        if ($conductor->empresa_id !== $empresaId) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        // Buscar qué vehículo activo maneja hoy este conductor
        $vehiculo = $conductor->vehiculos()->activos()->first();
        if (!$vehiculo) {
            return response()->json([
                'status' => 'warning',
                'message'=> "El conductor {$conductor->nombre} no tiene un vehículo activo asignado."
            ]);
        }

        // 1. Verificar si el conductor tiene una vuelta activa o si es inicio/fin
        $vueltaActiva = Vuelta::where('conductor_id', $conductor->id)
            ->where('estado', 'activa')
            ->latest()
            ->first();

        $tipo = $paradero->tipo;

        // Si es paradero de INICIO y no hay vuelta activa -> INICIAR VUELTA
        if ($tipo === 'origen' && !$vueltaActiva) {
            $ultimaVuelta = Vuelta::where('conductor_id', $conductor->id)
                ->whereDate('fecha', today())
                ->max('numero_vuelta') ?? 0;
            $numeroVuelta = $ultimaVuelta + 1;

            $vueltaActiva = Vuelta::create([
                'empresa_id'   => $empresaId,
                'vehiculo_id'  => $vehiculo->id,
                'conductor_id' => $conductor->id,
                'ruta_id'      => $paradero->ruta_id,
                'created_by'   => auth()->id(),
                'fecha'        => today(),
                'numero_vuelta'=> $numeroVuelta,
                'hora_salida'  => now()->format('H:i:s'),
                'estado'       => 'activa',
            ]);
        }

        // Registrar el Checkin
        $checkin = ParaderoCheckin::create([
            'empresa_id'       => $empresaId,
            'conductor_id'     => $conductor->id,
            'vehiculo_id'      => $vehiculo->id,
            'ruta_paradero_id' => $paradero->id,
            'vuelta_id'        => $vueltaActiva?->id,
            'hora_registro'    => now(),
            'tipo'             => $tipo,
            'exitoso'          => true,
            'observaciones'    => "Match facial distance: {$request->distancia}"
        ]);

        // Si es paradero de DESTINO (Fin de vuelta) -> TERMINAR VUELTA
        if ($tipo === 'destino' && $vueltaActiva) {
            $vueltaActiva->update([
                'hora_llegada' => now()->format('H:i:s'),
                'estado'       => 'completada'
            ]);
            $mensaje = "Vuelta #{$vueltaActiva->numero_vuelta} COMPETADA exitosamente.";
        } elseif ($tipo === 'origen') {
            $mensaje = "Vuelta #{$vueltaActiva->numero_vuelta} INICIADA exitosamente.";
        } else {
            $mensaje = "Control intermedio registrado correctamente.";
        }

        return response()->json([
            'status'   => 'success',
            'conductor'=> $conductor->nombre . ' ' . $conductor->apellido,
            'mensaje'  => $mensaje,
        ]);
    }
}
