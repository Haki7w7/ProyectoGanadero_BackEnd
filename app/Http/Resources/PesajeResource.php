<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Representación pública de un Pesaje.
 *
 * Los nombres de la izquierda son el contrato de la API: si mañana cambia una
 * columna de la base de datos, solo se ajusta el lado derecho y los clientes
 * siguen recibiendo exactamente los mismos campos.
 *
 * @mixin \App\Models\Pesaje
 */
class PesajeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'pesaje_id'     => $this->pesaje_id,
            'id_animal'     => $this->id_animal,
            'peso_kg'       => $this->peso_kg,
            'fecha_pesaje'  => $this->fecha_pesaje?->toJSON(),
            'observaciones' => $this->observaciones,
            // Resumen del animal, solo si la relación fue cargada.
            'animal'        => $this->whenLoaded('animal', fn () => [
                'id_animal'    => $this->animal->id_animal,
                'numero_arete' => $this->animal->numero_arete,
            ]),
            'created_at'    => $this->created_at?->toJSON(),
            'updated_at'    => $this->updated_at?->toJSON(),
        ];
    }
}
