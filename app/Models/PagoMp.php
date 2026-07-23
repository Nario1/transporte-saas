<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PagoMp extends Model
{
    protected $table = 'pagos_mp';

    protected $fillable = [
        'tributo_id',
        'sancion_id',
        'preference_id',
        'payment_id',
        'estado',
        'monto',
        'metodo',
        'webhook_data',
    ];

    protected $casts = [
        'webhook_data' => 'array',
        'monto'        => 'decimal:2',
    ];

    // ── Relaciones ──
    public function tributo(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Tributo::class);
    }

    public function sancion(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Sancion::class);
    }

    // ── Scopes ──
    public function scopeAprobados($q)  { return $q->where('estado', 'aprobado'); }
    public function scopePendientes($q) { return $q->where('estado', 'pendiente'); }

    // ── Accessors ──
    public function getEsAprobadoAttribute(): bool
    {
        return $this->estado === 'aprobado';
    }

    public function getEstadoBadgeAttribute(): string
    {
        return match ($this->estado) {
            'aprobado'   => '✅ Aprobado',
            'rechazado'  => '❌ Rechazado',
            'cancelado'  => '🚫 Cancelado',
            'en_proceso' => '⏳ En proceso',
            default      => '⏰ Pendiente',
        };
    }
}
