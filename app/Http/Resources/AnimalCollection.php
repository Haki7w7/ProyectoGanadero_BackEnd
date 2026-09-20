<?php

namespace App\Http\Resources;

use App\Support\PaginacionUniforme;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Colección paginada de animales.
 *
 * Reemplaza los metadatos por defecto de Laravel por la estructura uniforme
 * de la API (meta: current_page, last_page, per_page, total + links).
 */
class AnimalCollection extends ResourceCollection
{
    /**
     * Resource que transforma cada elemento de la colección.
     */
    public $collects = AnimalResource::class;

    public function paginationInformation(Request $request, array $paginated, array $default): array
    {
        return $this->resource instanceof LengthAwarePaginator
            ? PaginacionUniforme::desde($this->resource)
            : $default;
    }
}
