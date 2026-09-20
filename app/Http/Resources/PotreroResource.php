<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** Representación pública de un Potrero. @mixin \App\Models\Potrero */
class PotreroResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'potrero_id'             => $this->potrero_id,
            'nombre'                 => $this->nombre,
            'hectareas_de_extension' => $this->hectareas_de_extension,
            'capacidad_maxima'       => $this->capacidad_maxima,
            'estado_pasto'           => $this->estado_pasto,
            'created_at'             => $this->created_at?->toJSON(),
            'updated_at'             => $this->updated_at?->toJSON(),
        ];
    }
}
