<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** Representación pública de una Unidad de Medida. @mixin \App\Models\UnidadMedida */
class UnidadMedidaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'unidad_medida_id' => $this->unidad_medida_id,
            'nombre'           => $this->nombre,
            'apertura'         => $this->apertura,
            'created_at'       => $this->created_at?->toJSON(),
            'updated_at'       => $this->updated_at?->toJSON(),
        ];
    }
}
