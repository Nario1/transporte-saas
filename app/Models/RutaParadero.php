<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RutaParadero extends Model
{
    protected $fillable = ['ruta_id','nombre','tipo','orden'];
    public function ruta() { return $this->belongsTo(Ruta::class); }
}