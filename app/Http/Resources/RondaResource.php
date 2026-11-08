<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Ronda */
class RondaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'user_id'       => $this->user_id,
            'operador'      => UserResource::make($this->whenLoaded('user')),
            'fecha_inicio'  => $this->fecha_inicio?->toJSON(),
            'fecha_fin'     => $this->fecha_fin?->toJSON(),
            'estado'        => $this->estado,
            'observaciones' => $this->observaciones,
            'total_puntos'  => $this->puntos_ronda_count ?? ($this->relationLoaded('puntosRonda') ? $this->puntosRonda->count() : null),
            'puntos'        => PuntoRondaResource::collection($this->whenLoaded('puntosRonda')),
            'created_at'    => $this->created_at?->toJSON(),
            'updated_at'    => $this->updated_at?->toJSON(),
        ];
    }
}

