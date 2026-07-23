<?php

namespace App\Models;

use OwenIt\Auditing\Models\Audit as OwenItAudit;

class Audit extends OwenItAudit
{
    protected $fillable = [
        'user_type',
        'user_id',
        'event',
        'auditable_type',
        'auditable_id',
        'old_values',
        'new_values',
        'url',
        'ip_address',
        'user_agent',
        'tags',
        'empresa_id', // Añadido
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function getDescripcionAccionAttribute(): string
    {
        $usuario = $this->user->name ?? 'Sistema';
        $evento = match($this->event) {
            'created' => 'creó',
            'updated' => 'modificó',
            'deleted' => 'eliminó',
            'restored' => 'restauró',
            default => $this->event
        };

        $modelo = class_basename($this->auditable_type);
        
        // Traducción de modelos
        $nombres = [
            'User' => 'un Usuario',
            'Vehiculo' => 'un Vehículo',
            'Conductor' => 'un Conductor',
            'Propietario' => 'un Propietario',
            'Ruta' => 'una Ruta',
            'Vuelta' => 'una Vuelta',
            'Tributo' => 'un Tributo',
            'Sancion' => 'una Sanción',
            'Ajuste' => 'una Configuración',
            'Role' => 'un Rol',
            'Permission' => 'un Permiso',
            'Empresa' => 'una Empresa'
        ];

        $nombreModelo = $nombres[$modelo] ?? "el recurso $modelo";

        return "<strong>$usuario</strong> $evento $nombreModelo (ID: {$this->auditable_id})";
    }
}
