<?php

namespace App\Events;

use App\Models\AlertaOperativo;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AlertaOperativoCreada implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public AlertaOperativo $alerta) {}

    public function broadcastOn(): array
    {
        return [
            new \Illuminate\Broadcasting\PrivateChannel('empresa.' . $this->alerta->empresa_id . '.operativos'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'operativo.creado';
    }

    public function broadcastWith(): array
    {
        return [
            'alerta' => [
                'id'         => $this->alerta->id,
                'punto'      => $this->alerta->punto,
                'conductor'  => $this->alerta->conductor?->nombre_completo ?? 'Administrador',
                'creado_at'  => $this->alerta->created_at->format('h:i A'),
            ]
        ];
    }
}
