<?php

namespace App\Http\Controllers;

use App\Support\PaginacionUniforme;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;

abstract class Controller
{
    use AuthorizesRequests;
    protected const API_PREFIX = '/api/v1';

    protected function respuestaOk(mixed $data, string $mensaje, int $estado = 200): JsonResponse
    {
        return response()->json([
            'message' => $mensaje,
            'data'    => $this->resolver($data),
        ], $estado);
    }

    protected function respuestaCreada(Model $modelo, string $mensaje, string $recurso, mixed $data = null): JsonResponse
    {
        return response()
            ->json([
                'message' => $mensaje,
                'data'    => $this->resolver($data ?? $modelo),
            ], 201)
            ->header('Location', self::API_PREFIX.'/'.$recurso.'/'.$modelo->getKey());
    }

    protected function respuestaSinContenido(): Response
    {
        return response()->noContent();
    }

    protected function respuestaPaginada(LengthAwarePaginator $paginador, string $mensaje, ?string $recurso = null): JsonResponse
    {
        $items = $recurso !== null
            ? $recurso::collection($paginador->getCollection())->resolve(request())
            : $paginador->items();

        return response()->json(array_merge(
            ['message' => $mensaje, 'data' => $items],
            PaginacionUniforme::desde($paginador)
        ), 200);
    }

    private function resolver(mixed $data): mixed
    {
        return $data instanceof JsonResource ? $data->resolve(request()) : $data;
    }
}
