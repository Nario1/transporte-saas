<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParaderoCheckin extends Model
{
    protected $fillable = [
        'empresa_id',
        'conductor_id',
        'vehiculo_id',
        'ruta_paradero_id',
        'vuelta_id',
        'hora_registro',
        'tipo', // 'inicio', 'fin', 'intermedio'
        'exitoso', // booleano (true si pasó el face-api, false si falló pero igual se registró el intento)
        'observaciones'
    ];

    public function empresa() { return $this->belongsTo(Empresa::class); }
    public function conductor() { return $this->belongsTo(Conductor::class); }
    public function vehiculo() { return $this->belongsTo(Vehiculo::class); }
    public function rutaParadero() { return $this->belongsTo(RutaParadero::class); }
    public function vuelta() { return $this->belongsTo(Vuelta::class); }
}
