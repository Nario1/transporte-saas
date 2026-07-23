<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait Multitenantable
{
    /**
     * Boot del trait para asignar empresa_id automáticamente
     */
    public static function bootMultitenantable(): void
    {
        // Usamos la Fachada Auth para mayor compatibilidad con el editor
        if (Auth::check()) {
            static::creating(function ($model) {
                /** @var User $user */
                $user = Auth::user();
                
                if (empty($model->empresa_id)) {
                    $model->empresa_id = $user->empresa_id;
                }
            });
        }
    }

    /**
     * Scope para filtrar por empresa
     */
    public function scopeDeEmpresa(Builder $query): Builder
    {
        /** @var User|null $user */
        $user = Auth::user();

        // Si no hay usuario, devolvemos la consulta sin cambios (o vacía)
        if (!$user) {
            return $query;
        }

        return $query->where('empresa_id', $user->empresa_id);
    }
}