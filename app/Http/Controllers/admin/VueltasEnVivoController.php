<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vuelta;
use App\Models\Conductor;
use Illuminate\Http\Request;

class VueltasEnVivoController extends Controller
{
    /**
     * Vista del dashboard de vueltas en tiempo real.
     */
    public function index()
    {
        $empresaId = auth()->user()->empresa_id;

        $vueltasActivas = Vuelta::with(['conductor', 'vehiculo', 'ruta'])
            ->where('empresa_id', $empresaId)
            ->where('estado', 'activa')
            ->whereDate('fecha', today())
            ->orderBy('hora_salida')
            ->get();

        $totalConductoresActivos = $vueltasActivas->count();

        return view('admin.vueltas.en-vivo', compact('vueltasActivas', 'totalConductoresActivos'));
    }

    /**
     * API JSON para polling — /admin/api/vueltas-activas
     */
    public function activas()
    {
        $empresaId = auth()->user()->empresa_id;

        // Vueltas Activas
        $activas = Vuelta::with(['conductor', 'vehiculo', 'ruta'])
            ->where('empresa_id', $empresaId)
            ->where('estado', 'activa')
            ->whereDate('fecha', today())
            ->get();

        // Vueltas Terminadas Recientemente (últimos 30 min)
        $recientes = Vuelta::with(['conductor', 'vehiculo', 'ruta'])
            ->where('empresa_id', $empresaId)
            ->where('estado', 'completada')
            ->whereDate('fecha', today())
            ->where('updated_at', '>=', now()->subMinutes(30))
            ->get();

        $data = $activas->map(function (Vuelta $v) {
            $inicio   = \Carbon\Carbon::parse($v->fecha->format('Y-m-d') . ' ' . $v->hora_salida);
            $minutos  = $inicio->diffInMinutes(now());

            return [
                'id'            => $v->id,
                'conductor'     => $v->conductor?->nombre_completo ?? '—',
                'vehiculo'      => $v->vehiculo?->placa ?? '—',
                'flota'         => $v->vehiculo?->numero_flota ?? '?',
                'ruta'          => $v->ruta?->nombre ?? 'Sin ruta',
                'hora_salida'   => $v->hora_salida,
                'numero_vuelta' => $v->numero_vuelta,
                'latitud'       => $v->lat_actual ?? $v->latitud,
                'longitud'      => $v->lng_actual ?? $v->longitud,
                'lat_salida'    => $v->latitud,
                'lng_salida'    => $v->longitud,
                'lat_actual'    => $v->lat_actual,
                'lng_actual'    => $v->lng_actual,
                'inicio_ts'     => $inicio->timestamp * 1000,
                'hora_llegada'  => '—',
                'minutos_en_ruta' => $minutos,
                'estado'        => 'activa',
                'tiempo_label'  => $minutos < 60 ? "{$minutos} min" : floor($minutos / 60) . 'h ' . ($minutos % 60) . 'min',
            ];
        })->concat($recientes->map(function (Vuelta $v) {
            return [
                'id'            => $v->id,
                'conductor'     => $v->conductor?->nombre_completo ?? '—',
                'vehiculo'      => $v->vehiculo?->placa ?? '—',
                'flota'         => $v->vehiculo?->numero_flota ?? '?',
                'ruta'          => $v->ruta?->nombre ?? 'Sin ruta',
                'hora_salida'   => $v->hora_salida,
                'hora_llegada'  => $v->hora_llegada,
                'numero_vuelta' => $v->numero_vuelta,
                'latitud'       => $v->latitud,
                'longitud'      => $v->longitud,
                'lat_salida'    => $v->latitud,
                'lng_salida'    => $v->longitud,
                'latitud_fin'   => $v->latitud_fin,
                'longitud_fin'  => $v->longitud_fin,
                'estado'        => 'completada',
                'tiempo_total_msg' => (function() use ($v) {
                    if (!$v->hora_llegada) return '—';
                    $sec = \Carbon\Carbon::parse($v->hora_salida)->diffInSeconds(\Carbon\Carbon::parse($v->hora_llegada));
                    if ($sec < 60) return "$sec segundos";
                    if ($sec < 3600) return floor($sec/60) . " minutos";
                    return floor($sec/3600) . "h " . (floor($sec/60)%60) . "min";
                })(),
            ];
        }));

        return response()->json([
            'total_activas' => $activas->count(),
            'vueltas'       => $data,
            'hora'          => now()->format('H:i:s'),
        ]);
    }
}
