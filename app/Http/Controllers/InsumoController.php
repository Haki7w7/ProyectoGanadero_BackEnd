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

    public function index(Request $request): JsonResponse
    {
        $insumos = $this->insumoService->listarInsumos($request->query());

        return $this->respuestaPaginada($insumos, 'Listado de insumos obtenido correctamente.', InsumoResource::class);
    }

    public function show(int $id): JsonResponse
    {
        $insumo = $this->insumoService->obtenerPorId($id);

        return $this->respuestaOk(new InsumoResource($insumo), 'Insumo obtenido correctamente.');
    }

    public function store(StoreInsumoRequest $request): JsonResponse
    {
        $insumo = $this->insumoService->crearInsumo($request->validated());

        return $this->respuestaCreada($insumo, 'Insumo creado correctamente.', 'insumos', new InsumoResource($insumo));
    }

    public function update(UpdateInsumoRequest $request, int $id): JsonResponse
    {
        $insumo = $this->insumoService->actualizarInsumo($id, $request->validated());

        return $this->respuestaOk(new InsumoResource($insumo), 'Insumo actualizado correctamente.');
    }

    public function indexPorCategoria(Request $request, int $categoria): JsonResponse
    {
        $this->categoriaService->obtenerPorId($categoria);

        $insumos = $this->insumoService->listarInsumos(array_merge($request->query(), ['categoria_id' => $categoria]));

        return $this->respuestaPaginada($insumos, 'Listado de insumos de la categoría obtenido correctamente.', InsumoResource::class);
    }

    public function destroy($id): Response
    {
        $this->insumoService->eliminarInsumo((int) $id);

        return $this->respuestaSinContenido();
    }
}
