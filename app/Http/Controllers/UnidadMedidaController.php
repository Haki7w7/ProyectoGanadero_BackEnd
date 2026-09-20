<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUnidadMedidaRequest;
use App\Http\Requests\UpdateUnidadMedidaRequest;
use App\Services\UnidadMedidaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UnidadMedidaController extends Controller
{
    public function __construct(private readonly UnidadMedidaService $unidadMedidaService)
    {
    }

    // GET /api/unidad-medida
    public function index(Request $request): JsonResponse
    {
        $unidades = $this->unidadMedidaService->listarUnidadesMedida($request->query());

        return $this->respuestaPaginada($unidades, 'Listado de unidades de medida obtenido correctamente.');
    }

    // GET /api/unidad-medida/{id}
    public function show(int $id): JsonResponse
    {
        $unidad = $this->unidadMedidaService->obtenerPorId($id);

        return response()->json([
            'message' => 'Unidad de medida obtenida correctamente.',
            'data'    => $unidad,
        ], 200);
    }

    // POST /api/unidad-medida
    public function store(StoreUnidadMedidaRequest $request): JsonResponse
    {
        $unidad = $this->unidadMedidaService->crearUnidadMedida($request->validated());

        return $this->respuestaCreada($unidad, 'Unidad de medida creada correctamente.', 'unidad-medida');
    }

    // PUT/PATCH /api/unidad-medida/{id}
    public function update(UpdateUnidadMedidaRequest $request, int $id): JsonResponse
    {
        $unidad = $this->unidadMedidaService->actualizarUnidadMedida($id, $request->validated());

        return response()->json([
            'message' => 'Unidad de medida actualizada correctamente.',
            'data'    => $unidad,
        ], 200);
    }

    // DELETE /api/unidad-medida/{id}
    public function destroy($id): Response
    {
        $this->unidadMedidaService->eliminarUnidadMedida((int) $id);

        return $this->respuestaSinContenido();
    }
}
