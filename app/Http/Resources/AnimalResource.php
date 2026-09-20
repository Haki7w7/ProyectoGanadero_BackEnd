<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Representación pública de un Animal.
 *
 * Las relaciones (raza, potrero, pesajes, tratamientos) solo aparecen cuando
 * fueron cargadas por el servicio (whenLoaded), por lo que el listado es
 * liviano y el detalle es completo.
 *
 * @mixin \App\Models\Animal
 */
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
            'raza'             => $this->whenLoaded('raza', fn () => [
                'raza_id' => $this->raza->raza_id,
                'nombre'  => $this->raza->nombre,
            ]),
            'potrero'          => $this->whenLoaded('potrero', fn () => [
                'potrero_id'             => $this->potrero->potrero_id,
                'nombre'                 => $this->potrero->nombre,
                'hectareas_de_extension' => $this->potrero->hectareas_de_extension,
                'capacidad_maxima'       => $this->potrero->capacidad_maxima,
                'estado_pasto'           => $this->potrero->estado_pasto,
            ]),
            'pesajes'          => PesajeResource::collection($this->whenLoaded('pesajes')),
            'tratamientos'     => TratamientoResource::collection($this->whenLoaded('tratamientos')),
            'created_at'       => $this->created_at?->toJSON(),
            'updated_at'       => $this->updated_at?->toJSON(),
        ];
    }
}
