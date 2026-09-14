<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUnidadMedidaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre'   => ['sometimes', 'required', 'string', 'max:100'],
            'apertura' => ['nullable', 'string', 'max:20'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre de la unidad de medida es obligatorio.',
            'nombre.string'   => 'El nombre de la unidad de medida debe ser una cadena de texto.',
            'nombre.max'      => 'El nombre no puede exceder los 100 caracteres.',

            'apertura.string' => 'La apertura (abreviatura) debe ser una cadena de texto.',
            'apertura.max'    => 'La apertura no puede exceder los 20 caracteres.',
        ];
    }
}
