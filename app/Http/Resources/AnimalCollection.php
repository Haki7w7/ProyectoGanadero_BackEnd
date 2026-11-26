<?php

namespace App\Http\Resources;

use App\Support\PaginacionUniforme;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class AnimalCollection extends ResourceCollection
{
    public $collects = AnimalResource::class;

    public function paginationInformation(Request $request, array $paginated, array $default): array
    {
        return $this->resource instanceof LengthAwarePaginator
            ? PaginacionUniforme::desde($this->resource)
            : $default;
    }
}
