<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable; // la interfaz
use App\Traits\AuditableWithEmpresa;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ruta extends Model implements Auditable
{
    use SoftDeletes, AuditableWithEmpresa, \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected $fillable = ['empresa_id','nombre','codigo','origen','destino','estado','duracion_min','descripcion'];
    protected $auditInclude = ['nombre','origen','destino','estado'];

    public function empresa()   { return $this->belongsTo(Empresa::class); }
    public function paraderos() { return $this->hasMany(RutaParadero::class)->orderBy('orden'); }
    public function vehiculos() { return $this->belongsToMany(Vehiculo::class,'vehiculo_rutas')->withPivot('activo'); }
    public function vueltas()   { return $this->hasMany(Vuelta::class); }

    public function scopeDeEmpresa($q)
    {
        return $q->where('empresa_id', Auth::user()?->empresa_id ?? 0);
    }
    public function scopeActivas($q) { return $q->where('estado','activa'); }

    public function getNombreCompletoAttribute(): string {
        return "{$this->nombre} — {$this->origen} · {$this->destino}";
    }
}