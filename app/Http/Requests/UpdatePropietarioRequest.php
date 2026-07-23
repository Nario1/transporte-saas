<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePropietarioRequest extends FormRequest
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
            'nombre'    => 'required|string|max:100',
            'apellidos' => 'required|string|max:100',
            'dni'       => 'nullable|string|size:8',
            'telefono'  => 'nullable|string|max:15',
            'email'     => 'nullable|email|max:150',
            'direccion' => 'nullable|string|max:255',
            'activo'    => 'nullable|boolean',
            'notas'     => 'nullable|string',
        ];
    }
}
