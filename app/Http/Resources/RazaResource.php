<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RazaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'raza_id'    => $this->raza_id,
            'nombre'     => $this->nombre,
            'created_at' => $this->created_at?->toJSON(),
            'updated_at' => $this->updated_at?->toJSON(),
        ];
    }
}
