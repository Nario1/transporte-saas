<?php

namespace App\Services;

use App\Models\Conductor;
use App\Models\Vuelta;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class VueltaService
{
    /**
     * Inicia una nueva vuelta para el conductor.
     */
    public function iniciarVuelta(Conductor $conductor, ?int $rutaId, ?float $latitud, ?float $longitud, int $creadorId): Vuelta
    {
        $ultimaVuelta = Vuelta::where('conductor_id', $conductor->id)
            ->whereDate('fecha', today())
            ->max('numero_vuelta') ?? 0;
        $proximaVuelta = $ultimaVuelta + 1;

        $vuelta = DB::transaction(function () use ($conductor, $rutaId, $latitud, $longitud, $creadorId, $proximaVuelta) {
            $vehiculo = $conductor->vehiculos()->activos()->first();

            return Vuelta::create([
                'empresa_id'   => $conductor->empresa_id,
                'vehiculo_id'  => $vehiculo?->id ?? 1,
                'conductor_id' => $conductor->id,
                'ruta_id'      => $rutaId,
                'created_by'   => $creadorId,
                'fecha'        => today(),
                'numero_vuelta' => $proximaVuelta,
                'hora_salida'  => now()->format('H:i:s'),
                'latitud'      => $latitud,
                'longitud'     => $longitud,
                'estado'       => 'activa',
            ]);
        });

        Log::info("Vuelta iniciada #{$vuelta->numero_vuelta} por conductor {$conductor->id}");

        try {
            event(new \App\Events\VueltaIniciada($vuelta));
        } catch (\Throwable $e) {
            // WebSocket no configurado — ignorar
        }

        return $vuelta;
    }

    /**
     * Termina la vuelta activa y calcula la duración.
     * 
     * @return int Duración en minutos
     */
    public function terminarVuelta(Vuelta $vuelta, ?float $latitud, ?float $longitud): int
    {
        $vuelta->update([
            'hora_llegada' => now()->format('H:i:s'),
            'estado'       => 'completada',
            'latitud_fin'  => $latitud,
            'longitud_fin' => $longitud,
            'lat_actual'   => null,
            'lng_actual'   => null,
        ]);

        $inicio   = Carbon::parse($vuelta->fecha->format('Y-m-d') . ' ' . $vuelta->hora_salida);
        $fin      = Carbon::parse($vuelta->fecha->format('Y-m-d') . ' ' . $vuelta->hora_llegada);
        $duracion = (int) $inicio->diffInMinutes($fin);

        try {
            event(new \App\Events\VueltaTerminada($vuelta));
        } catch (\Throwable $e) {
            // WebSocket no configurado — ignorar
        }

        return $duracion;
    }
}
