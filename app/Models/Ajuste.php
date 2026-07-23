<?php

namespace App\Models;
use Illuminate\Foundation\Auth\User as Authenticatable;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;  
use Illuminate\Support\Facades\Auth;
 

use App\Traits\AuditableWithEmpresa;
use OwenIt\Auditing\Contracts\Auditable;

class Ajuste extends Model implements Auditable
{
    use AuditableWithEmpresa;
    protected $fillable = [
        'empresa_id','clave','valor','tipo','grupo','etiqueta','descripcion','es_publico',
    ];
    protected $casts = ['es_publico' => 'boolean'];

    public function empresa() { return $this->belongsTo(Empresa::class); }

    /** Lee valor casteado según tipo */
    public function getValorCasteadoAttribute(): mixed
    {
        return match($this->tipo) {
            'boolean' => (bool) $this->valor,
            'integer' => (int)  $this->valor,
            'json'    => json_decode($this->valor, true),
            default   => $this->valor,
        };
    }

    public static function get(string $clave, mixed $default = null): mixed
    {
        $ajuste = static::where('empresa_id', Auth::user()?->empresa_id ?? 0)
            ->where('clave', $clave)
            ->first();

        return $ajuste ? $ajuste->valor_casteado : $default;
    }

    public static function set(string $clave, mixed $valor, string $tipo = 'string'): void
    {
        static::updateOrCreate(
            ['empresa_id' => Auth::user()?->empresa_id ?? 0, 'clave' => $clave],
            ['valor' => $valor, 'tipo' => $tipo]
        );
    }
}



