<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PotreroResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $ocupacionActual = $this->ocupacion_actual !== null
            ? (int) $this->ocupacion_actual
            : $this->animales()->where('estado', '!=', 'Vendido')->count();

        return [
            'potrero_id'             => $this->potrero_id,
            'nombre'                 => $this->nombre,
            'codigo_qr'              => $this->codigo_qr,
            'hectareas_de_extension' => $this->hectareas_de_extension,
            'capacidad_maxima'       => $this->capacidad_maxima,
            'ocupacion_actual'       => $ocupacionActual,
            'espacios_disponibles'   => max(0, (int) $this->capacidad_maxima - $ocupacionActual),
            'estado_pasto'           => $this->estado_pasto,
            'created_at'             => $this->created_at?->toJSON(),
            'updated_at'             => $this->updated_at?->toJSON(),
        ];
    }
}
