<?php

namespace App\Http\Requests;

use App\Services\AnimalService;

class StorePesajeAnimalRequest extends StorePesajeRequest
{
    protected function prepareForValidation(): void
    {
        $animalId = (int) $this->route('animal');

        app(AnimalService::class)->verificarExistencia($animalId);

        $this->merge(['id_animal' => $animalId]);
    }
}
