<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FinalizarRondaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'observaciones' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'observaciones.string' => 'Las observaciones deben ser una cadena de texto.',
            'observaciones.max'    => 'Las observaciones no pueden exceder los 500 caracteres.',
        ];
    }
}

