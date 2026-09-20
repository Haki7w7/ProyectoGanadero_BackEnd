<?php

namespace App\Support;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Única fuente de verdad para los metadatos de paginación de la API.
 *
 * La usan tanto Controller::respuestaPaginada() como las clases
 * ResourceCollection (p. ej. AnimalCollection), así todos los listados
 * devuelven exactamente la misma estructura:
 *
 *   "meta":  { "current_page", "last_page", "per_page", "total" }
 *   "links": { "first", "last", "prev", "next" }
 */
final class PaginacionUniforme
{
    /**
     * @return array{meta: array<string, int>, links: array<string, string|null>}
     */
    public static function desde(LengthAwarePaginator $paginador): array
    {
        // Conserva filtros y orden (per_page, sort_by, raza_id, ...) en los enlaces.
        $paginador->appends(request()->query());

        return [
            'meta' => [
                'current_page' => $paginador->currentPage(),
                'last_page'    => $paginador->lastPage(),
                'per_page'     => $paginador->perPage(),
                'total'        => $paginador->total(),
            ],
            'links' => [
                'first' => $paginador->url(1),
                'last'  => $paginador->url($paginador->lastPage()),
                'prev'  => $paginador->previousPageUrl(),
                'next'  => $paginador->nextPageUrl(),
            ],
        ];
    }
}
