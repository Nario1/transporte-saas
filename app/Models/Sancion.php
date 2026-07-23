<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable; // la interfaz
use App\Traits\AuditableWithEmpresa;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sancion extends Model implements Auditable
{
    use SoftDeletes, AuditableWithEmpresa;
    protected $table = 'sanciones';
    protected $fillable = [
        'empresa_id','vehiculo_id','conductor_id',
        'registrado_por','cobrado_por',
        'fecha','motivo','descripcion','monto','estado','cobrado_at',
        'token_pago', 'metodo_pago',
        'motivo_exoneracion', 'exonerado_por', 'exonerado_at',
    ];
    protected $casts = [
        'fecha'      => 'date',
        'monto'      => 'decimal:2',
        'cobrado_at' => 'datetime',
        'exonerado_at' => 'datetime',
    ];
    protected $auditInclude = ['motivo','monto','estado','cobrado_por', 'motivo_exoneracion', 'exonerado_por'];

    public function empresa()       { return $this->belongsTo(Empresa::class); }
    public function vehiculo()      { return $this->belongsTo(Vehiculo::class); }
    public function conductor()     { return $this->belongsTo(Conductor::class); }
    public function registrador()   { return $this->belongsTo(User::class, 'registrado_por'); }
    public function cobrador()      { return $this->belongsTo(User::class, 'cobrado_por'); }
    public function exonerador()    { return $this->belongsTo(User::class, 'exonerado_por'); }

    public function pagoMp()        { return $this->hasOne(PagoMp::class, 'sancion_id'); }

    public function scopeDeEmpresa($q)
    {
        return $q->where('empresa_id', Auth::user()?->empresa_id ?? 0);
    }    
    
    public function scopePendientes($q) { return $q->where('estado','pendiente'); }
}
