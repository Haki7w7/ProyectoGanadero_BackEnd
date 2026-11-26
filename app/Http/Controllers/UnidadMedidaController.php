<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUnidadMedidaRequest;
use App\Http\Requests\UpdateUnidadMedidaRequest;
use App\Http\Resources\UnidadMedidaResource;
use App\Services\UnidadMedidaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UnidadMedidaController extends Controller
{
    public function __construct(private readonly UnidadMedidaService $unidadMedidaService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $unidades = $this->unidadMedidaService->listarUnidadesMedida($request->query());

        return $this->respuestaPaginada($unidades, 'Listado de unidades de medida obtenido correctamente.', UnidadMedidaResource::class);
    }

    public function show(int $id): JsonResponse
    {
        $unidad = $this->unidadMedidaService->obtenerPorId($id);

        return $this->respuestaOk(new UnidadMedidaResource($unidad), 'Unidad de medida obtenida correctamente.');
    }

    public function store(StoreUnidadMedidaRequest $request): JsonResponse
    {
        $unidad = $this->unidadMedidaService->crearUnidadMedida($request->validated());

        return $this->respuestaCreada($unidad, 'Unidad de medida creada correctamente.', 'unidades-medida', new UnidadMedidaResource($unidad));
    }

    public function update(UpdateUnidadMedidaRequest $request, int $id): JsonResponse
    {
        $unidad = $this->unidadMedidaService->actualizarUnidadMedida($id, $request->validated());

        return $this->respuestaOk(new UnidadMedidaResource($unidad), 'Unidad de medida actualizada correctamente.');
    }

    public function destroy($id): Response
    {
        $this->unidadMedidaService->eliminarUnidadMedida((int) $id);

        return $this->respuestaSinContenido();
    }
}
