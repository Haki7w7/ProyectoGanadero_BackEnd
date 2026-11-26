<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PesajeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'pesaje_id'     => $this->pesaje_id,
            'id_animal'     => $this->id_animal,
            'peso_kg'       => $this->peso_kg,
            'fecha_pesaje'  => $this->fecha_pesaje?->toJSON(),
            'observaciones' => $this->observaciones,
            'animal'        => $this->whenLoaded('animal', fn () => [
                'id_animal'    => $this->animal->id_animal,
                'numero_arete' => $this->animal->numero_arete,
            ]),
            'created_at'    => $this->created_at?->toJSON(),
            'updated_at'    => $this->updated_at?->toJSON(),
        ];
    }
}
