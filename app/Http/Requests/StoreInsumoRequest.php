<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInsumoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre'           => ['required', 'string', 'max:150'],
            'categoria_id'     => ['required', 'integer', 'exists:categorias,categoria_id'],
            'precio'           => ['required', 'numeric', 'min:0'],
            'unidad_medida_id' => ['required', 'integer', 'exists:unidades_medida,unidad_medida_id'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre del insumo es obligatorio.',
            'nombre.string'   => 'El nombre del insumo debe ser una cadena de texto.',
            'nombre.max'      => 'El nombre no puede exceder los 150 caracteres.',

            'categoria_id.required' => 'La categoría es obligatoria.',
            'categoria_id.integer'  => 'El identificador de la categoría debe ser un número entero.',
            'categoria_id.exists'   => 'La categoría seleccionada no existe.',

            'precio.required' => 'El precio es obligatorio.',
            'precio.numeric'  => 'El precio debe ser un valor numérico.',
            'precio.min'      => 'El precio no puede ser negativo.',

            'unidad_medida_id.required' => 'La unidad de medida es obligatoria.',
            'unidad_medida_id.integer'  => 'El identificador de la unidad de medida debe ser un número entero.',
            'unidad_medida_id.exists'   => 'La unidad de medida seleccionada no existe.',
        ];
    }
}
