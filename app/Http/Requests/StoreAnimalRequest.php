<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAnimalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Cambiado a true para permitir la petición
    }

    public function rules(): array
    {
        return [
            'numero_arete'     => ['required', 'string', 'max:50', 'unique:animales,numero_arete'],
            'raza_id'          => ['required', 'integer', 'exists:razas,raza_id'],
            'sexo'             => ['required', 'string', Rule::in(['Macho', 'Hembra'])],
            'fecha_nacimiento' => ['nullable', 'date', 'before_or_equal:today'],
            'estado'           => ['nullable', 'string', 'max:50'],
            'potrero_id'       => ['required', 'integer', 'exists:potreros,potrero_id'],
        ];
    }

    public function messages(): array
    {
        return [
            'numero_arete.required'            => 'El número de arete es obligatorio.',
            'numero_arete.unique'              => 'Ya existe un animal registrado con ese número de arete.',
            'numero_arete.max'                 => 'El número de arete no puede exceder los 50 caracteres.',
            'raza_id.required'                 => 'La raza es obligatoria.',
            'raza_id.exists'                   => 'La raza seleccionada no existe.',
            'sexo.required'                    => 'El sexo es obligatorio.',
            'sexo.in'                          => 'El sexo debe ser Macho o Hembra.',
            'fecha_nacimiento.date'            => 'La fecha de nacimiento no es una fecha válida.',
            'fecha_nacimiento.before_or_equal' => 'La fecha de nacimiento no puede ser futura.',
            'estado.max'                       => 'El estado no puede exceder los 50 caracteres.',
            'potrero_id.required'              => 'El potrero es obligatorio.',
            'potrero_id.exists'                => 'El potrero seleccionado no existe.',
        ];
    }
}