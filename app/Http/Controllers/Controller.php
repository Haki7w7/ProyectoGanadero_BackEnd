<?php

namespace App\Http\Controllers;

use App\Support\PaginacionUniforme;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;

/**
 * Controlador base de la API.
 *
 * Centraliza la semántica HTTP para que todos los recursos respondan igual:
 *  - Listados     -> 200 con { message, data, meta, links }
 *  - Creaciones   -> 201 + cabecera Location: /api/v1/{recurso}/{id}
 *  - Eliminación  -> 204 No Content (sin cuerpo)
 *
 * Los helpers aceptan tanto modelos/arreglos como JsonResource: si reciben un
 * Resource lo resuelven, de modo que el cuerpo siempre queda como
 * { "message": "...", "data": ... } sin el doble "data" que produce Laravel.
 */
abstract class Controller
{
    /**
     * Prefijo de la versión vigente de la API (usado para la cabecera Location).
     */
    protected const API_PREFIX = '/api/v1';

    /**
     * 200 OK con el sobre estándar { message, data }.
     */
    protected function respuestaOk(mixed $data, string $mensaje, int $estado = 200): JsonResponse
    {
        return response()->json([
            'message' => $mensaje,
            'data'    => $this->resolver($data),
        ], $estado);
    }

    /**
     * 201 Created con la cabecera Location apuntando al recurso recién creado.
     *
     * @param Model  $modelo  Registro creado (se usa su llave primaria para el Location).
     * @param string $recurso Segmento de la ruta del recurso, p. ej. "animales" o "unidad-medida".
     * @param mixed  $data    Cuerpo a devolver; por defecto el propio modelo.
     *                        Puede ser un JsonResource.
     */
    protected function respuestaCreada(Model $modelo, string $mensaje, string $recurso, mixed $data = null): JsonResponse
    {
        return response()
            ->json([
                'message' => $mensaje,
                'data'    => $this->resolver($data ?? $modelo),
            ], 201)
            ->header('Location', self::API_PREFIX.'/'.$recurso.'/'.$modelo->getKey());
    }

    /**
     * 204 No Content: eliminación exitosa, sin cuerpo.
     */
    protected function respuestaSinContenido(): Response
    {
        return response()->noContent();
    }

    /**
     * Listado paginado con estructura uniforme:
     *
     * {
     *   "message": "...",
     *   "data":  [ ... ],
     *   "meta":  { "current_page", "last_page", "per_page", "total" },
     *   "links": { "first", "last", "prev", "next" }
     * }
     *
     * @param class-string<JsonResource>|null $recurso
     *        Clase JsonResource opcional para transformar cada elemento.
     */
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

    /**
     * Si recibe un JsonResource lo convierte en arreglo; cualquier otro valor pasa igual.
     */
    private function resolver(mixed $data): mixed
    {
        return $data instanceof JsonResource ? $data->resolve(request()) : $data;
    }
}
