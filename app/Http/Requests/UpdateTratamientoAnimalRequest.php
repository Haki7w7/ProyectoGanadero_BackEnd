<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTratamientoAnimalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_animal'        => ['sometimes', 'required', 'integer', 'exists:animales,id_animal'],
            'tratamiento_id'   => ['sometimes', 'required', 'integer', 'exists:tratamientos,tratamiento_id'],
            'fecha_aplicacion' => ['sometimes', 'required', 'date'],
            'dosis_ml'         => ['sometimes', 'required', 'numeric', 'min:0', 'max:9999.99'],
            'observaciones'    => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'id_animal.required' => 'El animal es obligatorio.',
            'id_animal.integer'  => 'El identificador del animal debe ser un número entero.',
            'id_animal.exists'   => 'El animal seleccionado no existe.',

            'tratamiento_id.required' => 'El tratamiento es obligatorio.',
            'tratamiento_id.integer'  => 'El identificador del tratamiento debe ser un número entero.',
            'tratamiento_id.exists'   => 'El tratamiento seleccionado no existe.',

            'fecha_aplicacion.required' => 'La fecha de aplicación es obligatoria.',
            'fecha_aplicacion.date'     => 'La fecha de aplicación no es una fecha válida.',

            'dosis_ml.required' => 'La dosis es obligatoria.',
            'dosis_ml.numeric'  => 'La dosis debe ser un valor numérico.',
            'dosis_ml.min'      => 'La dosis no puede ser negativa.',
            'dosis_ml.max'      => 'La dosis no puede exceder 9999.99 ml.',

            'observaciones.string' => 'Las observaciones deben ser una cadena de texto.',
        ];
    }
}
