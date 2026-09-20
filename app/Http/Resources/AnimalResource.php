<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** Representación pública de un Animal; relaciones solo si fueron cargadas. @mixin \App\Models\Animal */
class AnimalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id_animal'        => $this->id_animal,
            'numero_arete'     => $this->numero_arete,
            'sexo'             => $this->sexo,
            'fecha_nacimiento' => $this->fecha_nacimiento?->toDateString(),
            'estado'           => $this->estado,
            'raza_id'          => $this->raza_id,
            'potrero_id'       => $this->potrero_id,
            'raza'             => RazaResource::make($this->whenLoaded('raza')),
            'potrero'          => PotreroResource::make($this->whenLoaded('potrero')),
            'pesajes'          => PesajeResource::collection($this->whenLoaded('pesajes')),
            'tratamientos'     => TratamientoResource::collection($this->whenLoaded('tratamientos')),
            'created_at'       => $this->created_at?->toJSON(),
            'updated_at'       => $this->updated_at?->toJSON(),
        ];
    }
}
