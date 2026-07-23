<?php

namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use OwenIt\Auditing\Contracts\Auditable;
use App\Traits\AuditableWithEmpresa;
 
class Conductor extends Model implements Auditable
{
    protected $table = 'conductores';

    use SoftDeletes, AuditableWithEmpresa, HasFactory;
 
    protected $fillable = [
        'empresa_id',
        'propietario_id',
        'nombre',
        'apellidos',
        'dni',
        'telefono',
        'email',
        'direccion',
        'tipo_licencia',
        'licencia_vence',
        'vigencia_examen_medico',  // ← nuevo
        'licencia_digital',        // ← nuevo
        'dni_digital',             // ← nuevo
        'primer_ingreso',          // ← nuevo
        'requiere_facial',         // ← nuevo (interruptor admin)
        'estado',
        'notas',
    ];
 
    protected $casts = [
        'licencia_vence'         => 'date',
        'vigencia_examen_medico' => 'date',
        'primer_ingreso'         => 'boolean',
        'requiere_facial'        => 'boolean',
    ];
 
    protected $auditInclude = [
        'nombre', 'apellidos', 'estado', 'tipo_licencia',
        'licencia_vence', 'vigencia_examen_medico',
    ];
 
    public function empresa()     { return $this->belongsTo(Empresa::class); }
    public function propietario() { return $this->belongsTo(Propietario::class); }
    public function user()        { return $this->hasOne(User::class); }
    public function vehiculos()   { return $this->hasMany(Vehiculo::class); }
    public function vueltas()     { return $this->hasMany(Vuelta::class); }
    public function tributos()    { return $this->hasMany(Tributo::class); }
    public function sanciones()   { return $this->hasMany(Sancion::class); }
    public function rostro()      { return $this->hasOne(ConductorRostro::class)->where('activo', true)->latest(); }
    public function rostros()     { return $this->hasMany(ConductorRostro::class); }
 
    // ── Scopes ──
    public function scopeDeEmpresa($q)
    {
        return $q->where('empresa_id', Auth::user()?->empresa_id ?? 0);
    }
 
    public function scopeActivos($q)
    {
        return $q->where('estado', 'activo');
    }
 
    // ── Accessors ──
    public function getNombreCompletoAttribute(): string
    {
        return trim("{$this->nombre} {$this->apellidos}");
    }
 
    public function getInicialesAttribute(): string
    {
        $parts = explode(' ', $this->nombre);
        return strtoupper(substr($parts[0], 0, 1) . substr($parts[1] ?? '', 0, 1));
    }
 
    public function getLicenciaVenceAlertaAttribute(): bool
    {
        return $this->licencia_vence &&
               $this->licencia_vence->isFuture() &&
               $this->licencia_vence->diffInDays(today()) <= 30;
    }
 
    public function getExamenMedicoAlertaAttribute(): bool
    {
        return $this->vigencia_examen_medico &&
               $this->vigencia_examen_medico->isFuture() &&
               $this->vigencia_examen_medico->diffInDays(today()) <= 30;
    }
 
    public function getTieneAccesoAttribute(): bool
    {
        return $this->user()->exists();
    }
}