<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCategoriaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:100'],
            'tipo'   => ['nullable', 'string', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre de la categoría es obligatorio.',
            'nombre.string'   => 'El nombre de la categoría debe ser una cadena de texto.',
            'nombre.max'      => 'El nombre no puede exceder los 100 caracteres.',

            'tipo.string' => 'El tipo debe ser una cadena de texto.',
            'tipo.max'    => 'El tipo no puede exceder los 50 caracteres.',
        ];
    }
}
