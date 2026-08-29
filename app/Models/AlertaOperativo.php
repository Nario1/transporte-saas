<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlertaOperativo extends Model
{
    protected $table = 'alertas_operativos';

    protected $fillable = [
        'empresa_id',
        'conductor_id',
        'user_id',
        'punto',
        'estado',
        'expires_at'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    /**
     * Relación con la empresa.
     */
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    /**
     * Relación con el conductor que reportó (si aplica).
     */
    public function conductor(): BelongsTo
    {
        return $this->belongsTo(Conductor::class);
    }

    /**
     * Relación con el usuario administrador que reportó (si aplica).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
