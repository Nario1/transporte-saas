<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Empresa extends Model implements Auditable
{
    use SoftDeletes, AuditableTrait;

    protected $fillable = [
        'nombre', 'ruc', 'razon_social', 'telefono', 'direccion',
        'plan', 'activa', 'logo_path', 'tributo_diario',
    ];

    protected $casts = [
        'activa'          => 'boolean',
        'tributo_diario'  => 'decimal:2',
    ];

    public function users()        { return $this->hasMany(User::class); }
    public function propietarios() { return $this->hasMany(Propietario::class); }
    public function conductores()  { return $this->hasMany(Conductor::class); }
    public function vehiculos()    { return $this->hasMany(Vehiculo::class); }
    public function rutas()        { return $this->hasMany(Ruta::class); }
    public function vueltas()      { return $this->hasMany(Vuelta::class); }
    public function tributos()     { return $this->hasMany(Tributo::class); }
    public function sanciones()    { return $this->hasMany(Sancion::class); }
    public function ajustes()      { return $this->hasMany(Ajuste::class); }

    /** Lee un ajuste de esta empresa con fallback */
    public function ajuste(string $clave, mixed $default = null): mixed
    {
        return $this->ajustes()->where('clave', $clave)->value('valor') ?? $default;
    }

    /** ¿Tiene el módulo habilitado? */
    public function moduloActivo(string $modulo): bool
    {
        return (bool) $this->ajuste("modulo.{$modulo}.activo", true);
    }
}
