<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePotreroRequest;
use App\Http\Requests\UpdatePotreroRequest;
use App\Http\Resources\PotreroResource;
use App\Models\Potrero;
use App\Services\PotreroService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * @tags Potreros
 */
class PotreroController extends Controller
{
    public function __construct(private readonly PotreroService $potreroService)
    {
    }

    /**
     * Listar potreros.
     *
     * Retorna el listado paginado de potreros de la finca con filtros por nombre o estado del pasto.
     *
     * @response 200 { "message": "Listado de potreros obtenido correctamente.", "data": {...} }
     */
    public function index(Request $request): JsonResponse
    {
        $potreros = $this->potreroService->listarPotreros($request->query());

        return $this->respuestaPaginada($potreros, 'Listado de potreros obtenido correctamente.', PotreroResource::class);
    }

    /**
     * Obtener detalle de un potrero.
     *
     * Retorna la información de un potrero específico por su ID.
     *
     * @response 200 { "message": "Potrero obtenido correctamente.", "data": {...} }
     * @response 404 { "error": "Recurso no encontrado" }
     */
    public function show(int $id): JsonResponse
    {
        $potrero = $this->potreroService->obtenerPorId($id);

        return $this->respuestaOk(new PotreroResource($potrero), 'Potrero obtenido correctamente.');
    }

    /**
     * Registrar un nuevo potrero.
     *
     * Crea un potrero en el sistema indicando capacidad, área y estado del pasto.
     *
     * @response 201 { "message": "Potrero creado correctamente.", "data": {...} }
     * @response 422 { "message": "Los datos proporcionados no son válidos." }
     */
    public function store(StorePotreroRequest $request): JsonResponse
    {
        $potrero = $this->potreroService->crearPotrero($request->validated());

        return $this->respuestaCreada($potrero, 'Potrero creado correctamente.', 'potreros', new PotreroResource($potrero));
    }

    /**
     * Actualizar datos de un potrero.
     *
     * Actualiza la información de un potrero existente.
     *
     * @response 200 { "message": "Potrero actualizado correctamente.", "data": {...} }
     * @response 404 { "error": "Recurso no encontrado" }
     * @response 422 { "message": "Los datos proporcionados no son válidos." }
     */
    public function update(UpdatePotreroRequest $request, int $id): JsonResponse
    {
        $potrero = $this->potreroService->actualizarPotrero($id, $request->validated());

        return $this->respuestaOk(new PotreroResource($potrero), 'Potrero actualizado correctamente.');
    }

    /**
     * Eliminar un potrero.
     *
     * Elimina el potrero indicado. Falla si el potrero tiene animales asignados actualmente (RN-02).
     *
     * @response 200 { "message": "Potrero eliminado correctamente." }
     * @response 404 { "error": "Recurso no encontrado" }
     * @response 409 { "error": "Regla de Negocio", "mensaje": "No se puede eliminar el potrero porque tiene animales asignados." }
     */
    public function destroy(int $id): Response
    {
        $this->potreroService->eliminarPotrero($id);

        return $this->respuestaSinContenido();
    }
}
