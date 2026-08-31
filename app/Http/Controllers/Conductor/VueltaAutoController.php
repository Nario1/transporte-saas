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

        // Rutas autorizadas: solo las que el vehículo del conductor tiene marcadas como activas
        // en la tabla vehiculo_rutas. Si no tiene vehículo o no tiene rutas asignadas, no podrá ver ninguna.
        $vehiculo = $conductor->vehiculos()->first();
        $rutas = collect();

        if ($vehiculo) {
            $rutas = $vehiculo->rutas()
                ->where('vehiculo_rutas.activo', true)
                ->where('rutas.estado', 'activa')
                ->with(['paraderos' => fn($q) => $q->orderBy('orden')])
                ->orderBy('rutas.nombre')
                ->get();
        }

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

        if ($conductor->requiere_facial && !$request->verificado_rostro) {
            return response()->json(['ok' => false, 'error' => 'La verificación facial es requerida para poder iniciar la vuelta.'], 422);
        }

        // Validar tramo geográfico del paradero de inicio
        $paradero = \App\Models\RutaParadero::findOrFail($request->ruta_paradero_id);
        if ($paradero->ruta_id != $request->ruta_id) {
            return response()->json(['ok' => false, 'error' => 'El paradero seleccionado no corresponde a la ruta.'], 422);
        }

        $dentroDeRango = $this->isPointWithinSegment(
            $request->latitud,
            $request->longitud,
            $paradero->latitud_a,
            $paradero->longitud_a,
            $paradero->latitud_b,
            $paradero->longitud_b,
            $paradero->tolerancia ?? 30
        );

        if (!$dentroDeRango) {
            return response()->json(['ok' => false, 'error' => 'No puedes iniciar la vuelta aquí. Tu GPS indica que estás fuera del tramo de coordenadas permitido para el paradero de inicio.'], 422);
        }

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
                auth()->id(),
                $request->ruta_paradero_id
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

        // Validar tramo geográfico de cualquiera de los paraderos permitidos
        $paraderos = \App\Models\RutaParadero::where('ruta_id', $vuelta->ruta_id)->get();
        $tieneCoordenadasAlguno = $paraderos->contains(fn($p) => !is_null($p->latitud_a));

        $paraderoFinalNombre = null;
        $paraderoFinalId = null;

        if ($tieneCoordenadasAlguno) {
            $dentroDeAlguno = false;
            foreach ($paraderos as $p) {
                if (!is_null($p->latitud_a) && $this->isPointWithinSegment($request->latitud, $request->longitud, $p->latitud_a, $p->longitud_a, $p->latitud_b, $p->longitud_b, $p->tolerancia ?? 30)) {
                    $dentroDeAlguno = true;
                    $paraderoFinalNombre = $p->nombre;
                    $paraderoFinalId = $p->id;
                    break;
                }
            }

            if (!$dentroDeAlguno) {
                return response()->json(['ok' => false, 'error' => 'No puedes terminar la vuelta aquí. Tu GPS indica que estás fuera del tramo de cualquiera de los paraderos permitidos de la ruta.'], 422);
            }
        }

        // Recibir GPS final
        $vuelta->latitud_fin  = $request->latitud;
        $vuelta->longitud_fin = $request->longitud;

        try {
            $duracion = $this->vueltaService->terminarVuelta(
                $vuelta,
                $request->latitud,
                $request->longitud,
                $paraderoFinalId
            );

            session()->flash('success', '¡Vuelta completada con éxito!');

            return response()->json([
                'ok'           => true,
                'hora_llegada' => $vuelta->hora_llegada,
                'duracion_min' => $duracion,
                'paradero'     => $paraderoFinalNombre,
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

        // Transmitir la actualización por WebSockets/Reverb de inmediato
        broadcast(new \App\Events\VueltaUbicacionActualizada(
            $vuelta,
            (float) $request->latitud,
            (float) $request->longitud
        ));

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

    private function isPointWithinSegment($latP, $lngP, $latA, $lngA, $latB, $lngB, $toleranceMeters = 30)
    {
        if (is_null($latA) || is_null($lngA) || is_null($latB) || is_null($lngB)) {
            return true; // Si no hay coordenadas configuradas, permitimos el paso (compatibilidad)
        }

        $latRef = ($latA + $latB) / 2;
        $degToRad = pi() / 180;
        
        // Escalar longitud por el coseno de la latitud promedio
        $scaleX = cos($latRef * $degToRad);
        
        // Vector AB
        $dy = $latB - $latA;
        $dx = ($lngB - $lngA) * $scaleX;
        
        // Vector AP
        $dyp = $latP - $latA;
        $dxp = ($lngP - $lngA) * $scaleX;
        
        // Cuadrado de la longitud de AB
        $ab2 = ($dx * $dx) + ($dy * $dy);
        if ($ab2 == 0) {
            return $this->haversineDistance($latP, $lngP, $latA, $lngA) <= $toleranceMeters;
        }
        
        // Factor de proyección t
        $ap_ab = ($dxp * $dx) + ($dyp * $dy);
        $t = $ap_ab / $ab2;
        
        // Verificar si la proyección cae dentro del segmento [0, 1]
        if ($t < 0 || $t > 1) {
            return false;
        }
        
        // Coordenadas del punto proyectado
        $latProj = $latA + $t * $dy;
        $lngProj = $lngA + $t * ($lngB - $lngA);
        
        // Distancia desde el punto P al punto proyectado en la calle
        $distance = $this->haversineDistance($latP, $lngP, $latProj, $lngProj);
        
        return $distance <= $toleranceMeters;
    }

    private function haversineDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // en metros

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
