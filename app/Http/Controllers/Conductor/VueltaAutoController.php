<?php

namespace App\Http\Controllers\Conductor;

use App\Http\Controllers\Controller;
use App\Models\Vuelta;
use App\Models\ConductorRostro;
use App\Http\Requests\IniciarVueltaAutoRequest;
use App\Http\Requests\TerminarVueltaAutoRequest;
use App\Services\VueltaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VueltaAutoController extends Controller
{
    public function __construct(private VueltaService $vueltaService) {}
    /**
     * GET /conductor/vuelta/iniciar
     * Muestra la pantalla de inicio de vuelta (con verificación facial).
     */
    public function iniciarVista()
    {
        $conductor = auth()->user()->conductor;
        if (! $conductor) abort(403, 'Sin perfil de conductor');

        // Verificar si ya tiene una vuelta activa
        $vueltaActiva = Vuelta::where('conductor_id', $conductor->id)
            ->where('estado', 'activa')
            ->latest()
            ->first();

        if ($vueltaActiva) {
            return redirect()->route('conductor.vuelta.activa')
                ->with('info', 'Ya tienes una vuelta en curso.');
        }

        // Verificar si tiene rostro registrado y si es REQUERIDO
        $requiereFacial = $conductor->requiere_facial;
        $tieneRostro    = ConductorRostro::where('conductor_id', $conductor->id)
            ->where('activo', true)
            ->exists();

        // Obtener el rostro para comparación
        $rostro = $tieneRostro 
            ? ConductorRostro::where('conductor_id', $conductor->id)->where('activo',true)->latest()->first() 
            : null;

        // Próximo número de vuelta de hoy
        $ultimaVuelta = Vuelta::where('conductor_id', $conductor->id)
            ->whereDate('fecha', today())
            ->max('numero_vuelta') ?? 0;
        $proximaVuelta = $ultimaVuelta + 1;

        // Rutas disponibles para la empresa
        $rutas = \App\Models\Ruta::where('empresa_id', $conductor->empresa_id)
            ->where('estado', 'activa')
            ->orderBy('nombre')
            ->get();

        return view('users.vuelta.iniciar', compact(
            'conductor', 'tieneRostro', 'rostro', 'proximaVuelta', 'rutas', 'requiereFacial'
        ));
    }

    /**
     * POST /conductor/vuelta/iniciar
     */
    public function iniciar(IniciarVueltaAutoRequest $request)
    {
        $conductor = auth()->user()->conductor;
        if (! $conductor) abort(403);

        $request->validated();

        $yaActiva = Vuelta::where('conductor_id', $conductor->id)
            ->where('estado', 'activa')
            ->exists();

        if ($yaActiva) {
            return response()->json(['ok' => false, 'error' => 'Ya tienes una vuelta activa.'], 422);
        }

        try {
            $vuelta = $this->vueltaService->iniciarVuelta(
                $conductor,
                $request->ruta_id,
                $request->latitud,
                $request->longitud,
                auth()->id()
            );

            return response()->json([
                'ok'           => true,
                'vuelta_id'    => $vuelta->id,
                'numero_vuelta' => $vuelta->numero_vuelta,
                'hora_salida'  => $vuelta->hora_salida,
                'redirect'     => route('conductor.vuelta.activa'),
            ]);
        } catch (\Throwable $e) {
            Log::error('Error iniciando vuelta: ' . $e->getMessage());
            return response()->json(['ok' => false, 'error' => 'Error al registrar la vuelta.'], 500);
        }
    }

    /**
     * GET /conductor/vuelta/activa
     */
    public function activaVista()
    {
        $conductor = auth()->user()->conductor;
        $vuelta = Vuelta::where('conductor_id', $conductor->id)
            ->where('estado', 'activa')
            ->latest()
            ->first();

        if (! $vuelta) {
            return redirect()->route('conductor.vuelta.iniciar')
                ->with('info', 'No tienes una vuelta activa.');
        }

        return view('users.vuelta.activa', compact('vuelta', 'conductor'));
    }

    /**
     * POST /conductor/vuelta/terminar
     */
    public function terminar(Request $request)
    {
        $conductor = auth()->user()->conductor;

        $vuelta = Vuelta::where('conductor_id', $conductor->id)
            ->where('estado', 'activa')
            ->latest()
            ->first();

        if (! $vuelta) {
            return response()->json(['ok' => false, 'error' => 'No tienes una vuelta activa.'], 422);
        }

        // Recibir GPS final
        $vuelta->latitud_fin  = $request->latitud;
        $vuelta->longitud_fin = $request->longitud;

        try {
            $duracion = $this->vueltaService->terminarVuelta(
                $vuelta,
                $request->latitud,
                $request->longitud
            );

            session()->flash('success', '¡Vuelta completada con éxito!');

            return response()->json([
                'ok'           => true,
                'hora_llegada' => $vuelta->hora_llegada,
                'duracion_min' => $duracion,
                'redirect'     => route('conductor.vueltas'),
            ]);
        } catch (\Throwable $e) {
            Log::error('Error terminando vuelta: ' . $e->getMessage());
            return response()->json(['ok' => false, 'error' => 'Error al registrar la llegada.'], 500);
        }
    }

    /**
     * POST /conductor/vuelta/actualizar-ubicacion
     */
    public function actualizarUbicacion(Request $request)
    {
        $conductor = auth()->user()->conductor;
        $vuelta = Vuelta::where('conductor_id', $conductor->id)
            ->where('estado', 'activa')
            ->latest()
            ->first();

        if (!$vuelta) {
            return response()->json(['ok' => false], 404);
        }

        $vuelta->update([
            'lat_actual' => $request->latitud,
            'lng_actual' => $request->longitud,
        ]);

        return response()->json(['ok' => true]);
    }

    /**
     * GET /conductor/vuelta/estado (JSON)
     * Devuelve el estado de la vuelta activa del conductor.
     */
    public function estado()
    {
        $conductor = auth()->user()->conductor;
        $vuelta = Vuelta::where('conductor_id', $conductor->id)
            ->where('estado', 'activa')
            ->with(['ruta'])
            ->latest()
            ->first();

        return response()->json([
            'activa'       => (bool) $vuelta,
            'vuelta'       => $vuelta ? [
                'id'           => $vuelta->id,
                'numero'        => $vuelta->numero_vuelta,
                'hora_salida'  => $vuelta->hora_salida,
                'ruta'         => $vuelta->ruta?->nombre,
            ] : null,
        ]);
    }
}
