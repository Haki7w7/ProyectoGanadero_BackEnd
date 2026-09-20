<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** Representación pública de un Insumo. @mixin \App\Models\Insumo */
class InsumoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'insumo_id'        => $this->insumo_id,
            'nombre'           => $this->nombre,
            'categoria_id'     => $this->categoria_id,
            'precio'           => $this->precio,
            'unidad_medida_id' => $this->unidad_medida_id,
            'categoria'        => CategoriaResource::make($this->whenLoaded('categoria')),
            'unidad_medida'    => UnidadMedidaResource::make($this->whenLoaded('unidadMedida')),
            'created_at'       => $this->created_at?->toJSON(),
            'updated_at'       => $this->updated_at?->toJSON(),
        ];
    }
}
