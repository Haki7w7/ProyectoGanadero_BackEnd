<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAnimalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Obtenemos el ID del animal desde la ruta para la regla de unicidad
        $animalId = $this->route('animal')->id_animal ?? $this->route('animal');

        return [
            'numero_arete' => [
                'sometimes', 'required', 'string', 'max:50',
                Rule::unique('animales', 'numero_arete')->ignore($animalId, 'id_animal'),
            ],
            'raza_id'          => ['sometimes', 'required', 'integer', 'exists:razas,raza_id'],
            'sexo'             => ['sometimes', 'required', 'string', Rule::in(['Macho', 'Hembra'])],
            'fecha_nacimiento' => ['nullable', 'date', 'before_or_equal:today'],
            'estado'           => ['nullable', 'string', 'max:50'],
            'potrero_id'       => ['sometimes', 'required', 'integer', 'exists:potreros,potrero_id'],
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