<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePropietarioRequest extends FormRequest
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
        return [
            'nombre'         => 'required|string|max:100',
            'apellidos'      => 'required|string|max:100',
            'dni'            => 'nullable|string|size:8',
            'telefono'       => 'nullable|string|max:15',
            'email'          => 'nullable|email|max:150',
            'direccion'      => 'nullable|string|max:255',
            'notas'          => 'nullable|string',
            'monto_inicial'  => 'nullable|numeric|min:0|max:600',
            'cuota_1'        => 'nullable|numeric|min:0|max:600',
            'cuota_2'        => 'nullable|numeric|min:0|max:600',
            'cuota_3'        => 'nullable|numeric|min:0|max:600',
            // Dinámicos socio-conductor
            'es_conductor'              => 'nullable|boolean',
            'tipo_licencia'             => 'exclude_unless:es_conductor,1|required|string|max:10',
            'licencia_vence'            => 'exclude_unless:es_conductor,1|required|date',
            'carnet_habilitacion_tipo'  => 'exclude_unless:es_conductor,1|nullable|string|max:50',
            'carnet_habilitacion_vence' => 'exclude_unless:es_conductor,1|nullable|date',
            'conductor_estado'          => 'exclude_unless:es_conductor,1|required|string|in:activo,suspendido,inactivo',
        ];
    }
}
