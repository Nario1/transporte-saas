<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Backup extends Model
{
    protected $fillable = ['empresa_id', 'filename', 'path', 'size', 'type'];

    public function empresa()
    {
        return $this->belongsTo(\App\Models\Empresa::class);
    }
}
