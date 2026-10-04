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
    'string' => 'El campo :attribute debe ser una cadena de texto.',
    'integer' => 'El campo :attribute debe ser un número entero.',
    'numeric' => 'El campo :attribute debe ser un número.',
    'boolean' => 'El campo :attribute debe ser verdadero o falso.',
    'email' => 'El campo :attribute debe ser una dirección de correo válida.',
    'date' => 'El campo :attribute no es una fecha válida.',
    'array' => 'El campo :attribute debe ser un arreglo.',

    'min' => [
        'numeric' => 'El campo :attribute debe ser como mínimo :min.',
        'string' => 'El campo :attribute debe tener al menos :min caracteres.',
        'array' => 'El campo :attribute debe tener al menos :min elementos.',
    ],

    'max' => [
        'numeric' => 'El campo :attribute no debe ser mayor que :max.',
        'string' => 'El campo :attribute no debe tener más de :max caracteres.',
        'array' => 'El campo :attribute no debe tener más de :max elementos.',
    ],

    'between' => [
        'numeric' => 'El campo :attribute debe estar entre :min y :max.',
        'string' => 'El campo :attribute debe tener entre :min y :max caracteres.',
    ],

    'size' => [
        'numeric' => 'El campo :attribute debe ser :size.',
        'string' => 'El campo :attribute debe tener :size caracteres.',
    ],

    'in' => 'El campo :attribute seleccionado no es válido.',
    'unique' => 'El campo :attribute ya ha sido registrado.',
    'exists' => 'El campo :attribute seleccionado no es válido.',
    'confirmed' => 'La confirmación de :attribute no coincide.',

    // Nombres de atributos
    // Sin esta tabla Laravel imprime el nombre crudo de la columna
    // ("El campo numero_arete es obligatorio."). Con ella, un texto legible.
    'attributes' => [
        // Animales
        'numero_arete' => 'número de arete',
        'raza_id' => 'raza',
        'potrero_id' => 'potrero',
        'sexo' => 'sexo',
        'fecha_nacimiento' => 'fecha de nacimiento',
        'estado' => 'estado',

        // Potreros
        'nombre' => 'nombre',
        'hectareas_de_extension' => 'hectáreas de extensión',
        'capacidad_maxima' => 'capacidad máxima',
        'estado_pasto' => 'estado del pasto',

        // Pesajes
        'id_animal' => 'animal',
        'peso_kg' => 'peso',
        'fecha_pesaje' => 'fecha del pesaje',
        'observaciones' => 'observaciones',

        // Catálogos
        'tipo' => 'tipo',
        'descripcion' => 'descripción',
        'categoria_id' => 'categoría',
        'precio' => 'precio',
        'unidad_medida_id' => 'unidad de medida',
        'abreviatura' => 'abreviatura',
        'precio_min' => 'precio mínimo',
        'precio_max' => 'precio máximo',
        'fecha_desde' => 'fecha desde',
        'fecha_hasta' => 'fecha hasta',
        'peso_min' => 'peso mínimo',
        'peso_max' => 'peso máximo',
        'per_page' => 'cantidad de registros por página',
        'sort_by' => 'campo de ordenamiento',
        'order' => 'sentido de ordenamiento',

        // Tratamientos
        'tratamiento_id' => 'tratamiento',
        'fecha_aplicacion' => 'fecha de aplicación',
        'dosis_ml' => 'dosis',

        // Autenticación
        'name' => 'nombre',
        'email' => 'correo electrónico',
        'password' => 'contraseña',
        'password_confirmation' => 'confirmación de contraseña',
        'role' => 'rol',
    ],

];
