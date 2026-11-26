<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TratamientoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'tratamiento_id' => $this->tratamiento_id,
            'nombre'         => $this->nombre,
            'descripcion'    => $this->descripcion,
            'tipo'           => $this->tipo,
            'aplicacion'     => $this->whenPivotLoaded('tratamiento_animal', fn () => [
                'tratamiento_animal_id' => $this->pivot->tratamiento_animal_id,
                'fecha_aplicacion'      => $this->pivot->fecha_aplicacion?->toJSON(),
                'dosis_ml'              => $this->pivot->dosis_ml,
                'observaciones'         => $this->pivot->observaciones,
            ]),
            'created_at'     => $this->created_at?->toJSON(),
            'updated_at'     => $this->updated_at?->toJSON(),
        ];
    }
}
