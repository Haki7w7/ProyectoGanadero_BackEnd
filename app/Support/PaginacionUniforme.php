<?php

namespace App\Support;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class PaginacionUniforme
{
    public static function desde(LengthAwarePaginator $paginador): array
    {
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
