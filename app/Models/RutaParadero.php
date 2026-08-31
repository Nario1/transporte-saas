<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RutaParadero extends Model
{
    protected $table = 'ruta_paraderos';

    protected $fillable = [
        'ruta_id',
        'nombre',
        'tipo',
        'orden',
        'latitud_a',
        'longitud_a',
        'latitud_b',
        'longitud_b',
        'tolerancia',
    ];

    protected $casts = [
        'latitud_a'  => 'float',
        'longitud_a' => 'float',
        'latitud_b'  => 'float',
        'longitud_b' => 'float',
        'tolerancia' => 'integer',
    ];

    public function ruta()
    {
        return $this->belongsTo(Ruta::class);
    }
}