<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInsumoRequest;
use App\Http\Requests\UpdateInsumoRequest;
use App\Services\InsumoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InsumoController extends Controller
{
    public function __construct(private readonly InsumoService $insumoService)
    {
    }

    // GET /api/insumos
    public function index(Request $request): JsonResponse
    {
        $insumos = $this->insumoService->listarInsumos($request->query());

        return response()->json([
            'message' => 'Listado de insumos obtenido correctamente.',
            'data'    => $insumos,
        ], 200);
    }

    // GET /api/insumos/{id}
    public function show(int $id): JsonResponse
    {
        $insumo = $this->insumoService->obtenerPorId($id);

        return response()->json([
            'message' => 'Insumo obtenido correctamente.',
            'data'    => $insumo,
        ], 200);
    }

    // POST /api/insumos
    public function store(StoreInsumoRequest $request): JsonResponse
    {
        $insumo = $this->insumoService->crearInsumo($request->validated());

        return response()->json([
            'message' => 'Insumo creado correctamente.',
            'data'    => $insumo,
        ], 201);
    }

    // PUT/PATCH /api/insumos/{id}
    public function update(UpdateInsumoRequest $request, int $id): JsonResponse
    {
        $insumo = $this->insumoService->actualizarInsumo($id, $request->validated());

        return response()->json([
            'message' => 'Insumo actualizado correctamente.',
            'data'    => $insumo,
        ], 200);
    }

    // DELETE /api/insumos/{id}
    public function destroy($id): JsonResponse
    {
        $this->insumoService->eliminarInsumo((int) $id);

        return response()->json([
            'message' => 'Insumo eliminado correctamente.',
        ], 200);
    }
}
