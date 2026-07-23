<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use Illuminate\Support\Facades\Auth;

class VehiculoRuta extends Pivot implements Auditable
{
    use AuditableTrait;

    protected $table = 'vehiculo_rutas';

    /**
     * Los pivots no tienen autoincrement por defecto,
     * pero si quieres auditoría owen-it necesita PK.
     * Puedes agregar $incrementing = true y un id en la migración,
     * o dejarlo sin auditoría (quitar implements Auditable y el trait).
     */
    public $incrementing = false;

    protected $fillable = [
        'vehiculo_id',
        'ruta_id',
        'activo',
        'fecha_asignacion',
    ];

    protected $casts = [
        'activo'           => 'boolean',
        'fecha_asignacion' => 'date',
    ];

    // ── Auditoría: solo campos relevantes ──
    protected $auditInclude = ['activo', 'fecha_asignacion'];

    // ── Relaciones ──

    public function vehiculo(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Vehiculo::class);
    }

    public function ruta(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Ruta::class);
    }

    // ── Scopes ──

    public function scopeActivos($q)
    {
        return $q->where('activo', true);
    }

     public function scopeDeEmpresa($q)
    {
        return $q->whereHas('vehiculo', fn($v) =>
            $v->where('empresa_id', Auth::user()?->empresa_id ?? 0)
        );
    }
}