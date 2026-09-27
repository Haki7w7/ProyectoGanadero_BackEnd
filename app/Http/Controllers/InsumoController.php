<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInsumoRequest;
use App\Http\Requests\UpdateInsumoRequest;
use App\Http\Resources\InsumoResource;
use App\Services\CategoriaService;
use App\Services\InsumoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class InsumoController extends Controller
{
    public function __construct(
        private readonly InsumoService $insumoService,
        private readonly CategoriaService $categoriaService,
    ) {
    }

    /**
     * Listar insumos.
     *
     * Retorna el listado paginado de insumos agrícolas o veterinarios registrados.
     */
    public function index(Request $request): JsonResponse
    {
        $insumos = $this->insumoService->listarInsumos($request->query());

        return $this->respuestaPaginada($insumos, 'Listado de insumos obtenido correctamente.', InsumoResource::class);
    }

    /**
     * Obtener detalle de un insumo.
     *
     * Retorna la información de un insumo específico por su ID.
     */
    public function show(int $id): JsonResponse
    {
        $insumo = $this->insumoService->obtenerPorId($id);

        return $this->respuestaOk(new InsumoResource($insumo), 'Insumo obtenido correctamente.');
    }

    /**
     * Registrar un nuevo insumo.
     *
     * Valida los datos y registra un nuevo insumo en el inventario.
     */
    public function store(StoreInsumoRequest $request): JsonResponse
    {
        $insumo = $this->insumoService->crearInsumo($request->validated());

        return $this->respuestaCreada($insumo, 'Insumo creado correctamente.', 'insumos', new InsumoResource($insumo));
    }

    /**
     * Actualizar datos de un insumo.
     *
     * Actualiza la información y existencias de un insumo existente por su ID.
     */
    public function update(UpdateInsumoRequest $request, int $id): JsonResponse
    {
        $insumo = $this->insumoService->actualizarInsumo($id, $request->validated());

        return $this->respuestaOk(new InsumoResource($insumo), 'Insumo actualizado correctamente.');
    }

    // GET /api/v1/categorias/{categoria}/insumos
    public function indexPorCategoria(Request $request, int $categoria): JsonResponse
    {
        $this->categoriaService->obtenerPorId($categoria);

        $insumos = $this->insumoService->listarInsumos(array_merge($request->query(), ['categoria_id' => $categoria]));

        return $this->respuestaPaginada($insumos, 'Listado de insumos de la categoría obtenido correctamente.', InsumoResource::class);
    }

    /**
     * Eliminar un insumo.
     *
     * Elimina el insumo indicado del sistema por su ID.
     */
    public function destroy($id): Response
    {
        $this->insumoService->eliminarInsumo((int) $id);

        return $this->respuestaSinContenido();
    }
}
