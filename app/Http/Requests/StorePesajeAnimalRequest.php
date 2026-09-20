<?php

namespace App\Http\Requests;

use App\Services\AnimalService;

/**
 * Validación para POST /api/v1/animales/{animal}/pesajes.
 *
 * Reutiliza las reglas de StorePesajeRequest, pero el animal NO viaja en el
 * cuerpo: se toma de la URL. Cualquier "id_animal" enviado en el cuerpo se
 * ignora para que el pesaje no pueda registrarse en un animal distinto al de la ruta.
 */
class StorePesajeAnimalRequest extends StorePesajeRequest
{
    protected function prepareForValidation(): void
    {
        $animalId = (int) $this->route('animal');

        // Si el animal de la URL no existe se responde 404 (y no un 422 de validación).
        app(AnimalService::class)->verificarExistencia($animalId);

        $this->merge(['id_animal' => $animalId]);
    }
}
