<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use OwenIt\Auditing\Contracts\Auditable; // la interfaz
use App\Traits\AuditableWithEmpresa;

class Tributo extends Model implements Auditable
{
    use SoftDeletes, AuditableWithEmpresa;

    protected $fillable = [
        'empresa_id','vehiculo_id','conductor_id','cobrado_por',
        'fecha','monto','metodo_pago','estado','cobrado_at','observaciones','token_pago',
        'motivo_exoneracion', 'exonerado_por', 'exonerado_at',
    ];
    protected $casts = [
        'fecha'      => 'date',
        'monto'      => 'decimal:2',
        'cobrado_at' => 'datetime',
        'exonerado_at' => 'datetime',
    ];
    protected $auditInclude = ['estado','monto','metodo_pago','cobrado_por','cobrado_at', 'motivo_exoneracion', 'exonerado_por'];

    public function empresa()    { return $this->belongsTo(Empresa::class); }
    public function vehiculo()   { return $this->belongsTo(Vehiculo::class); }
    public function conductor()  { return $this->belongsTo(Conductor::class); }
    public function cobrador()   { return $this->belongsTo(User::class, 'cobrado_por'); }
    public function exonerador() { return $this->belongsTo(User::class, 'exonerado_por'); }
    public function pagoMp()     { return $this->hasOne(PagoMp::class); }

    public function scopeDeEmpresa($q)
    {
        return $q->where('empresa_id', Auth::user()?->empresa_id ?? 0);
    }
    public function scopePagados($q)    { return $q->where('estado','pagado'); }
    public function scopePendientes($q) { return $q->where('estado','pendiente'); }
    public function scopeHoy($q)        { return $q->whereDate('fecha', today()); }
    public function scopeConToken($q)   { return $q->whereNotNull('token_pago'); }
    
    /**
     * Asegura que los tributos existan para todos los vehículos activos de una empresa.
     */
    public static function ensureGenerados(int $empresaId, int $diasAtras = 30): int
    {
        $vehiculos = Vehiculo::where('empresa_id', $empresaId)
            ->where('estado', 'activo')
            ->get();

        $empresa = \App\Models\Empresa::find($empresaId);
        $montoDiario = $empresa->tributo_diario ?? 24.00;
        $generados   = 0;

        for ($i = 0; $i < $diasAtras; $i++) {
            $fecha = today()->subDays($i);

            foreach ($vehiculos as $vehiculo) {
                if ($vehiculo->created_at->isAfter($fecha->endOfDay())) {
                    continue;
                }

                $creado = self::firstOrCreate(
                    [
                        'empresa_id'  => $empresaId,
                        'vehiculo_id' => $vehiculo->id,
                        'fecha'       => $fecha->toDateString(),
                    ],
                    [
                        'conductor_id' => $vehiculo->conductor_id,
                        'monto'        => $montoDiario,
                        'estado'       => 'pendiente',
                    ]
                );

                if ($creado->wasRecentlyCreated) {
                    $generados++;
                }
            }
        }

        return $generados;
    }
}