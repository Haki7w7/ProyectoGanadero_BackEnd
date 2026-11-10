<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePotreroRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre'                 => ['required', 'string', 'max:100'],
            'codigo_qr'              => ['nullable', 'string', 'max:100', 'unique:potreros,codigo_qr'],
            'hectareas_de_extension' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'capacidad_maxima'       => ['required', 'integer', 'min:0'],
            'estado_pasto'           => ['nullable', 'string', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required'                 => 'El nombre del potrero es obligatorio.',
            'nombre.max'                      => 'El nombre no puede exceder los 100 caracteres.',
            'codigo_qr.string'                => 'El código QR debe ser una cadena de texto.',
            'codigo_qr.max'                   => 'El código QR no puede exceder los 100 caracteres.',
            'codigo_qr.unique'                => 'Ya existe un potrero registrado con ese código QR.',
            'hectareas_de_extension.required' => 'Las hectáreas de extensión son obligatorias.',
            'hectareas_de_extension.numeric'  => 'Las hectáreas de extensión deben ser un valor numérico.',
            'hectareas_de_extension.min'      => 'Las hectáreas de extensión no pueden ser negativas.',
            'capacidad_maxima.required'       => 'La capacidad máxima es obligatoria.',
            'capacidad_maxima.integer'        => 'La capacidad máxima debe ser un número entero.',
            'capacidad_maxima.min'            => 'La capacidad máxima no puede ser negativa.',
            'estado_pasto.max'                => 'El estado del pasto no puede exceder los 50 caracteres.',
        ];
    }
}
