<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRazaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => ['sometimes', 'required', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre de la raza es obligatorio.',
            'nombre.string'   => 'El nombre de la raza debe ser una cadena de texto.',
            'nombre.max'      => 'El nombre no puede exceder los 100 caracteres.',
        ];
    }
}
