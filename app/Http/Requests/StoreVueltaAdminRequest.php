<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreVueltaAdminRequest extends FormRequest
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
        // Dynamic catch-all for validated() behavior
        $keys = array_keys($this->all());
        $rules = [];
        foreach ($keys as $key) {
            if (!in_array($key, ['_token', '_method'])) {
                $rules[$key] = 'nullable';
            }
        }
        return $rules;
    }
}
