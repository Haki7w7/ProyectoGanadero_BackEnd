<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Representación pública de una aplicación de tratamiento a un animal
 * (registro del historial sanitario).
 *
 * @mixin \App\Models\TratamientoAnimal
 */
class TratamientoAnimalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'tratamiento_animal_id' => $this->tratamiento_animal_id,
            'id_animal'             => $this->id_animal,
            'tratamiento_id'        => $this->tratamiento_id,
            'fecha_aplicacion'      => $this->fecha_aplicacion?->toJSON(),
            'dosis_ml'              => $this->dosis_ml,
            'observaciones'         => $this->observaciones,
            'animal'                => $this->whenLoaded('animal', fn () => [
                'id_animal'    => $this->animal->id_animal,
                'numero_arete' => $this->animal->numero_arete,
            ]),
            'tratamiento'           => TratamientoResource::make($this->whenLoaded('tratamiento')),
            'created_at'            => $this->created_at?->toJSON(),
            'updated_at'            => $this->updated_at?->toJSON(),
        ];
    }
}
