<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Auth;

use Carbon\Carbon;

use App\Traits\AuditableWithEmpresa;
use OwenIt\Auditing\Contracts\Auditable;

class Vehiculo extends Model implements Auditable
{
    use HasFactory, AuditableWithEmpresa, SoftDeletes;

    protected $fillable = [
        'empresa_id', 'propietario_id', 'conductor_id',
        'placa', 'numero_flota',                          // ← número de flota
        'marca', 'modelo', 'color', 'anio',
        'numero_motor', 'numero_chasis',
        'soat_vence', 'rev_tecnica_vence', 'tarjeta_prop_vence',
        'estado', 'notas',
    ];
    protected $casts = [
        'soat_vence'           => 'date',
        'rev_tecnica_vence'    => 'date',
        'tarjeta_prop_vence'   => 'date',
    ];
    protected $auditInclude = [
        'placa','numero_flota','conductor_id','propietario_id',
        'estado','soat_vence','rev_tecnica_vence',
        'marca', 'modelo', 'color', 'anio', 'numero_motor', 'numero_chasis',
    ];

    public function empresa()     { return $this->belongsTo(Empresa::class); }
    public function propietario() { return $this->belongsTo(Propietario::class); }
    public function conductor()   { return $this->belongsTo(Conductor::class); }
    public function rutas()       { return $this->belongsToMany(Ruta::class, 'vehiculo_rutas')->withPivot('activo','fecha_asignacion'); }
    public function vueltas()     { return $this->hasMany(Vuelta::class); }
    public function tributos()    { return $this->hasMany(Tributo::class); }
    public function sanciones()   { return $this->hasMany(Sancion::class); }

    public function scopeDeEmpresa($q)
    {
        return $q->where('empresa_id', Auth::user()?->empresa_id ?? 0);
    }
    public function scopeActivos($q)      { return $q->where('estado', 'activo'); }
    public function scopeConDeuda($q)     {
        return $q->whereHas('tributos', fn($t) => $t->where('estado','pendiente'));
    }

    // ── Accessors ──

    /** Placa en mayúsculas formateada */
    public function getPlacaFormAttribute(): string {
        return strtoupper($this->placa);
    }

    /** Label de flota: "Nro. 12" o null */
    public function getNumeroFlotaLabelAttribute(): ?string {
        return $this->numero_flota ? "Nro. {$this->numero_flota}" : null;
    }

    /** Marca y Modelo juntas */
    public function getBrandModelAttribute(): string {
        return trim("{$this->marca} {$this->modelo} {$this->anio}");
    }

    /** ¿Tiene tributo pagado hoy? */
    public function getTributoPagadoHoyAttribute(): bool {
        return $this->tributos()
            ->whereDate('fecha', today())
            ->where('estado', 'pagado')
            ->exists();
    }

    /** ¿SOAT próximo a vencer (<= 15 días)? */
    public function getSoatAlertaAttribute(): bool {
        return $this->soat_vence &&
               $this->soat_vence->isFuture() &&
               $this->soat_vence->diffInDays(today()) <= 15;
    }

    /** ¿Rev. técnica próxima a vencer (<= 15 días)? */
    public function getRevAlertaAttribute(): bool {
        return $this->rev_tecnica_vence &&
               $this->rev_tecnica_vence->isFuture() &&
               $this->rev_tecnica_vence->diffInDays(today()) <= 15;
    }

    /** ¿Algún documento vencido? */
    public function getDocVencidoAttribute(): bool {
        return ($this->soat_vence && $this->soat_vence->isPast()) ||
               ($this->rev_tecnica_vence && $this->rev_tecnica_vence->isPast());
    }

    /**
     * Obtener el historial de placas anteriores a partir de las auditorías de actualización,
     * incluyendo todos los datos modificados.
     */
    public function getHistorialPlacasAttribute(): array
    {
        $updateAudits = $this->audits
            ->where('event', 'updated')
            ->sortByDesc('created_at');

        $conductorIds = [];
        $propietarioIds = [];
        foreach ($updateAudits as $audit) {
            $oldValues = $audit->old_values;
            $newValues = $audit->new_values;

            if (is_string($oldValues)) {
                $oldValues = json_decode($oldValues, true);
            }
            if (is_string($newValues)) {
                $newValues = json_decode($newValues, true);
            }

            if (!isset($oldValues['placa'])) {
                continue;
            }

            if (isset($oldValues['conductor_id'])) $conductorIds[] = $oldValues['conductor_id'];
            if (isset($newValues['conductor_id'])) $conductorIds[] = $newValues['conductor_id'];
            if (isset($oldValues['propietario_id'])) $propietarioIds[] = $oldValues['propietario_id'];
            if (isset($newValues['propietario_id'])) $propietarioIds[] = $newValues['propietario_id'];
        }

        $conductoresMap = [];
        if (!empty($conductorIds)) {
            $conductoresMap = Conductor::withTrashed()
                ->whereIn('id', array_unique(array_filter($conductorIds)))
                ->get()
                ->pluck('nombre_completo', 'id')
                ->toArray();
        }

        $propietariosMap = [];
        if (!empty($propietarioIds)) {
            $propietariosMap = Propietario::withTrashed()
                ->whereIn('id', array_unique(array_filter($propietarioIds)))
                ->get()
                ->pluck('nombre_completo', 'id')
                ->toArray();
        }

        return $updateAudits
            ->map(function ($audit) use ($conductoresMap, $propietariosMap) {
                $oldValues = $audit->old_values;
                $newValues = $audit->new_values;

                if (is_string($oldValues)) {
                    $oldValues = json_decode($oldValues, true);
                }
                if (is_string($newValues)) {
                    $newValues = json_decode($newValues, true);
                }

                if (!isset($oldValues['placa'])) {
                    return null;
                }

                $modificaciones = [];

                $etiquetas = [
                    'placa'               => 'Placa',
                    'marca'               => 'Marca',
                    'modelo'              => 'Modelo',
                    'color'               => 'Color',
                    'anio'                => 'Año',
                    'numero_flota'        => 'Padrón / N° Flota',
                    'numero_motor'        => 'N° Motor',
                    'numero_chasis'       => 'N° Chasis',
                    'soat_vence'          => 'SOAT Vence',
                    'rev_tecnica_vence'   => 'Rev. Técnica Vence',
                    'tarjeta_prop_vence'  => 'Tarj. Propiedad Vence',
                    'estado'              => 'Estado',
                    'conductor_id'        => 'Conductor',
                    'propietario_id'      => 'Propietario',
                ];

                foreach ($oldValues as $campo => $valorAnterior) {
                    if (!isset($etiquetas[$campo])) {
                        continue;
                    }

                    $valorNuevo = $newValues[$campo] ?? null;

                    // Formatear conductores y propietarios
                    if ($campo === 'conductor_id') {
                        $valorAnterior = $conductoresMap[$valorAnterior] ?? ($valorAnterior ? "ID: $valorAnterior" : '(Sin Conductor)');
                        $valorNuevo = $conductoresMap[$valorNuevo] ?? ($valorNuevo ? "ID: $valorNuevo" : '(Sin Conductor)');
                    } elseif ($campo === 'propietario_id') {
                        $valorAnterior = $propietariosMap[$valorAnterior] ?? ($valorAnterior ? "ID: $valorAnterior" : '(Sin Propietario)');
                        $valorNuevo = $propietariosMap[$valorNuevo] ?? ($valorNuevo ? "ID: $valorNuevo" : '(Sin Propietario)');
                    }

                    // Formatear fechas
                    if (in_array($campo, ['soat_vence', 'rev_tecnica_vence', 'tarjeta_prop_vence'])) {
                        if ($valorAnterior) {
                            try {
                                $valorAnterior = $valorAnterior instanceof \Carbon\Carbon ? $valorAnterior->format('d/m/Y') : \Carbon\Carbon::parse($valorAnterior)->format('d/m/Y');
                            } catch (\Exception $e) {}
                        }
                        if ($valorNuevo) {
                            try {
                                $valorNuevo = $valorNuevo instanceof \Carbon\Carbon ? $valorNuevo->format('d/m/Y') : \Carbon\Carbon::parse($valorNuevo)->format('d/m/Y');
                            } catch (\Exception $e) {}
                        }
                    }

                    $modificaciones[] = [
                        'campo'    => $etiquetas[$campo],
                        'anterior' => $valorAnterior ?? '(Vacío)',
                        'nuevo'    => $valorNuevo ?? '(Vacío)',
                    ];
                }

                return [
                    'placa_anterior' => $oldValues['placa'],
                    'placa_nueva'    => $newValues['placa'] ?? null,
                    'marca'          => $oldValues['marca'] ?? null,
                    'modelo'         => $oldValues['modelo'] ?? null,
                    'color'          => $oldValues['color'] ?? null,
                    'anio'           => $oldValues['anio'] ?? null,
                    'fecha_cambio'   => $audit->created_at,
                    'usuario'        => $audit->user?->name ?? 'Sistema',
                    'modificaciones' => $modificaciones,
                ];
            })
            ->filter()
            ->values()
            ->toArray();
    }
}