<?php

namespace App\Events;

use App\Models\Vuelta;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VueltaTerminada implements ShouldBroadcast
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
        return 'vuelta.terminada';
    }

    public function broadcastWith(): array
    {
        $inicio  = \Carbon\Carbon::parse($this->vuelta->fecha->format('Y-m-d') . ' ' . $this->vuelta->hora_salida);
        $fin     = \Carbon\Carbon::parse($this->vuelta->fecha->format('Y-m-d') . ' ' . $this->vuelta->hora_llegada);
        $duracion = $inicio->diffInMinutes($fin);

        return [
            'vuelta_id'    => $this->vuelta->id,
            'conductor'    => $this->vuelta->conductor?->nombre_completo,
            'hora_llegada' => $this->vuelta->hora_llegada,
            'duracion_min' => $duracion,
        ];
    }
}
