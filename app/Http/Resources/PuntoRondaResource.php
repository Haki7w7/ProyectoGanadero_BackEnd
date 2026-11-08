<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\PuntoRonda */
class PuntoRondaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'ronda_id'       => $this->ronda_id,
            'potrero_id'     => $this->potrero_id,
            'latitud'        => (float) $this->latitud,
            'longitud'       => (float) $this->longitud,
            'estado_potrero' => $this->estado_potrero,
            'observacion'    => $this->observacion,
            'registrado_en'  => $this->registrado_en?->toJSON(),
            'potrero'        => PotreroResource::make($this->whenLoaded('potrero')),
        ];
    }
}

