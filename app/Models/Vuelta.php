<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable; // la interfaz
use App\Traits\AuditableWithEmpresa;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vuelta extends Model implements Auditable
{
    use SoftDeletes, AuditableWithEmpresa;

    protected $fillable = [
        'empresa_id','vehiculo_id','conductor_id','ruta_id','paradero_salida_id','paradero_llegada_id','created_by',
        'fecha', 'numero_vuelta', 'hora_salida', 'hora_llegada', 'observaciones',
        'latitud', 'longitud', 'lat_actual', 'lng_actual', 'latitud_fin', 'longitud_fin', 'estado',
    ];
    protected $casts = [
        'fecha'        => 'date',
        'latitud'      => 'decimal:7',
        'longitud'     => 'decimal:7',
        'lat_actual'   => 'decimal:7',
        'lng_actual'   => 'decimal:7',
        'latitud_fin'  => 'decimal:7',
        'longitud_fin' => 'decimal:7',
    ];
    protected $auditInclude = ['vehiculo_id','conductor_id','ruta_id','fecha','numero_vuelta'];

    public function empresa()    { return $this->belongsTo(Empresa::class); }
    public function vehiculo()   { return $this->belongsTo(Vehiculo::class); }
    public function conductor()  { return $this->belongsTo(Conductor::class); }
    public function ruta()       { return $this->belongsTo(Ruta::class); }
    public function paraderoSalida() { return $this->belongsTo(RutaParadero::class, 'paradero_salida_id'); }
    public function paraderoLlegada() { return $this->belongsTo(RutaParadero::class, 'paradero_llegada_id'); }
    public function creadoPor()  { return $this->belongsTo(User::class, 'created_by'); }

    public function scopeDeEmpresa($q)
    {
        return $q->where('empresa_id', Auth::user()?->empresa_id ?? 0);
    }
    public function scopeHoy($q)       { return $q->whereDate('fecha', today()); }
    public function scopeDelDia($q, $fecha) { return $q->whereDate('fecha', $fecha); }
    public function scopeActivas($q)   { return $q->where('estado', 'activa'); }
    public function scopeCompletadas($q) { return $q->where('estado', 'completada'); }
}
