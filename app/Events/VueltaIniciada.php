<?php

namespace App\Events;

use App\Models\Vuelta;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VueltaIniciada implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Vuelta $vuelta) {}

    public function broadcastOn(): array
    {
        return [
            new \Illuminate\Broadcasting\PrivateChannel('empresa.' . $this->vuelta->empresa_id . '.vueltas'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'vuelta.iniciada';
    }

    public function broadcastWith(): array
    {
        return [
            'vuelta' => [
                'id'            => $this->vuelta->id,
                'hora_salida'   => $this->vuelta->hora_salida,
                'numero_vuelta' => $this->vuelta->numero_vuelta,
                'latitud'       => $this->vuelta->latitud,
                'longitud'      => $this->vuelta->longitud,
                'conductor'     => [
                    'nombre_completo' => $this->vuelta->conductor?->nombre_completo,
                    'dni'             => $this->vuelta->conductor?->dni,
                ],
                'vehiculo'      => [
                    'placa' => $this->vuelta->vehiculo?->placa,
                ],
                'ruta'          => [
                    'nombre' => $this->vuelta->ruta?->nombre,
                ],
            ]
        ];
    }
}
