<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePesajeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_animal'     => ['sometimes', 'required', 'integer', 'exists:animales,id_animal'],
            'peso_kg'       => ['sometimes', 'required', 'numeric', 'min:0', 'max:9999.99'],
            'fecha_pesaje'  => ['sometimes', 'required', 'date'],
            'observaciones' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'id_animal.required' => 'El animal es obligatorio.',
            'id_animal.integer'  => 'El identificador del animal debe ser un número entero.',
            'id_animal.exists'   => 'El animal seleccionado no existe.',

            'peso_kg.required' => 'El peso es obligatorio.',
            'peso_kg.numeric'  => 'El peso debe ser un valor numérico.',
            'peso_kg.min'      => 'El peso no puede ser negativo.',
            'peso_kg.max'      => 'El peso no puede exceder 9999.99 kg.',

            'fecha_pesaje.required' => 'La fecha del pesaje es obligatoria.',
            'fecha_pesaje.date'     => 'La fecha del pesaje no es una fecha válida.',

            'observaciones.string' => 'Las observaciones deben ser una cadena de texto.',
        ];
    }
}
