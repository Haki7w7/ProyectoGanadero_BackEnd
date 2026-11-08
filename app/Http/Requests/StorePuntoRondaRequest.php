<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePuntoRondaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'codigo_qr'      => ['required', 'string', 'max:100', 'exists:potreros,codigo_qr'],
            'latitud'        => ['required', 'numeric', 'between:-90,90'],
            'longitud'       => ['required', 'numeric', 'between:-180,180'],
            'estado_potrero' => ['required', 'string', 'in:bueno,regular,requiere_atencion'],
            'observacion'    => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'codigo_qr.required'      => 'El código QR del potrero es obligatorio.',
            'codigo_qr.exists'        => 'El potrero con el código QR escaneado no existe.',
            'latitud.required'        => 'La coordenada de latitud es obligatoria.',
            'latitud.numeric'         => 'La latitud debe ser un valor numérico decimal.',
            'latitud.between'         => 'La latitud debe encontrarse entre -90 y 90 grados.',
            'longitud.required'       => 'La coordenada de longitud es obligatoria.',
            'longitud.numeric'        => 'La longitud debe ser un valor numérico decimal.',
            'longitud.between'        => 'La longitud debe encontrarse entre -180 y 180 grados.',
            'estado_potrero.required' => 'El estado del potrero es obligatorio.',
            'estado_potrero.in'       => 'El estado del potrero debe ser bueno, regular o requiere_atencion.',
            'observacion.string'      => 'La observación debe ser una cadena de texto.',
            'observacion.max'         => 'La observación no puede exceder los 500 caracteres.',
        ];
    }
}

