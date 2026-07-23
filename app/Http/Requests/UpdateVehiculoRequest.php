<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVehiculoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $id = $this->route('vehiculo')?->id;
        return [
            'placa'              => 'required|string|max:8|unique:vehiculos,placa,' . $id,
            'numero_flota'       => [
                'nullable',
                'integer',
                Rule::unique('vehiculos', 'numero_flota')->ignore($id)->where(function ($query) {
                    return $query->where('empresa_id', auth()->user()->empresa_id);
                })
            ],
            'estado'             => 'required|string|in:activo,inactivo,mantenimiento',
            'marca'              => 'nullable|string|max:50',
            'modelo'             => 'nullable|string|max:50',
            'color'              => 'nullable|string|max:30',
            'anio'               => 'nullable|integer|min:1980|max:' . (date('Y') + 1),
            'soat_vence'         => 'nullable|date',
            'rev_tecnica_vence'  => 'nullable|date',
            'tarjeta_prop_vence' => 'nullable|date',
            'propietario_id'     => 'nullable|integer|exists:propietarios,id',
            'conductor_id'       => 'nullable|integer|exists:conductores,id',
            'rutas'              => 'nullable|array',
            'rutas.*'            => 'integer|exists:rutas,id',
        ];
    }
}
