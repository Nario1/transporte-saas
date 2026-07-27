<?php

namespace App\Events;

use App\Models\Vuelta;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VueltaUbicacionActualizada implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Vuelta $vuelta,
        public float $latitud,
        public float $longitud
    ) {}

    public function broadcastOn(): array
    {
        return [
            new \Illuminate\Broadcasting\PrivateChannel('empresa.' . $this->vuelta->empresa_id . '.vueltas'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'vuelta.ubicacion_actualizada';
    }

    public function broadcastWith(): array
    {
        return [
            'vuelta_id' => $this->vuelta->id,
            'latitud'   => $this->latitud,
            'longitud'  => $this->longitud,
        ];
    }
}
