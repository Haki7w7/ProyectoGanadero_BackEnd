<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */
    'required' => 'El campo :attribute es obligatorio.',
    'string'   => 'El campo :attribute debe ser una cadena de texto.',
    'integer'  => 'El campo :attribute debe ser un número entero.',
    'numeric'  => 'El campo :attribute debe ser un número.',
    'boolean'  => 'El campo :attribute debe ser verdadero o falso.',
    'email'    => 'El campo :attribute debe ser una dirección de correo válida.',
    'date'     => 'El campo :attribute no es una fecha válida.',
    'array'    => 'El campo :attribute debe ser un arreglo.',

    'min' => [
        'numeric' => 'El campo :attribute debe ser como mínimo :min.',
        'string'  => 'El campo :attribute debe tener al menos :min caracteres.',
        'array'   => 'El campo :attribute debe tener al menos :min elementos.',
    ],

    'max' => [
        'numeric' => 'El campo :attribute no debe ser mayor que :max.',
        'string'  => 'El campo :attribute no debe tener más de :max caracteres.',
        'array'   => 'El campo :attribute no debe tener más de :max elementos.',
    ],

    'between' => [
        'numeric' => 'El campo :attribute debe estar entre :min y :max.',
        'string'  => 'El campo :attribute debe tener entre :min y :max caracteres.',
    ],

    'size' => [
        'numeric' => 'El campo :attribute debe ser :size.',
        'string'  => 'El campo :attribute debe tener :size caracteres.',
    ],

    'in'      => 'El campo :attribute seleccionado no es válido.',
    'unique'  => 'El campo :attribute ya ha sido registrado.',
    'exists'  => 'El campo :attribute seleccionado no es válido.',
    'confirmed' => 'La confirmación de :attribute no coincide.',

    // Nombres de atributos
    // Remplazan: atribute por un nombre que se pueda comprender.
    'attributes' => [
        'nombre'   => 'nombre',
        'email'    => 'correo electrónico',
        'password' => 'contraseña',
        'edad'     => 'edad',
    ],

];
