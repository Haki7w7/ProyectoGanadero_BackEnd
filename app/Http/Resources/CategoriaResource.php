<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** Representación pública de una Categoría. @mixin \App\Models\Categoria */
class CategoriaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'categoria_id' => $this->categoria_id,
            'nombre'       => $this->nombre,
            'tipo'         => $this->tipo,
            'created_at'   => $this->created_at?->toJSON(),
            'updated_at'   => $this->updated_at?->toJSON(),
        ];
    }
}
