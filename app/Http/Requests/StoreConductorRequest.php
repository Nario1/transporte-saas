<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreConductorRequest extends FormRequest
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
            'telefono'       => 'nullable|string|size:9',
            'email'          => 'nullable|email|max:255',
            'propietario_id' => 'nullable|integer|exists:propietarios,id',
            'tipo_licencia'  => 'required|string|max:10',
            'licencia_vence' => 'nullable|date',
            'estado'         => 'required|string|in:activo,suspendido,inactivo',
            'direccion'      => 'nullable|string|max:255',
            'notas'          => 'nullable|string',
        ];
    }
}
