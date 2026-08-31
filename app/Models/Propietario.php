<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use OwenIt\Auditing\Contracts\Auditable; // la interfaz
use App\Traits\AuditableWithEmpresa;
use Illuminate\Database\Eloquent\SoftDeletes;

class Propietario extends Model implements Auditable
{
    use SoftDeletes, AuditableWithEmpresa, HasFactory;

    protected $fillable = [
        'empresa_id', 'nombre', 'apellidos', 'dni', 'telefono',
        'telefono_alt', 'email', 'direccion', 'activo', 'notas',
        'monto_inicial', 'cuota_1', 'cuota_2', 'cuota_3',
    ];
    protected $casts = [
        'activo'        => 'boolean',
        'monto_inicial' => 'float',
        'cuota_1'       => 'float',
        'cuota_2'       => 'float',
        'cuota_3'       => 'float',
    ];

    // Auditoría: solo campos relevantes
    protected $auditInclude = ['nombre','apellidos','dni','telefono','activo','monto_inicial','cuota_1','cuota_2','cuota_3'];

    public function empresa()   { return $this->belongsTo(Empresa::class); }
    public function vehiculos() { return $this->hasMany(Vehiculo::class); }
    public function conductor() { return $this->hasOne(Conductor::class); }

    public function scopeDeEmpresa($q)
    {
        return $q->where('empresa_id', Auth::user()?->empresa_id ?? 0);
    }

    public function getNombreCompletoAttribute(): string
    {
        return trim("{$this->nombre} {$this->apellidos}");
    }

    public function getMontoIngresoTotalAttribute(): float
    {
        if ($this->vehiculos()->count() === 0) {
            return (float) (($this->monto_inicial ?? 0) + ($this->cuota_1 ?? 0) + ($this->cuota_2 ?? 0) + ($this->cuota_3 ?? 0));
        }
        return (float) $this->vehiculos->sum('monto_ingreso_total');
    }

    public function getEstadoIngresoAttribute(): string
    {
        if ($this->vehiculos()->count() === 0) {
            return $this->monto_ingreso_total >= 600 ? 'PAGADO' : 'DEUDA';
        }
        return $this->monto_ingreso_deuda > 0 ? 'DEUDA' : 'PAGADO';
    }

    public function getMontoIngresoDeudaAttribute(): float
    {
        if ($this->vehiculos()->count() === 0) {
            return (float) max(0, 600 - $this->monto_ingreso_total);
        }
        return (float) $this->vehiculos->sum('monto_ingreso_deuda');
    }
}
