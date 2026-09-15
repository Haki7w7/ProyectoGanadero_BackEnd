<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRazaRequest;
use App\Http\Requests\UpdateRazaRequest;
use App\Services\RazaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RazaController extends Controller
{
    public function __construct(private readonly RazaService $razaService)
    {
    }

    // GET /api/razas
    public function index(Request $request): JsonResponse
    {
        $razas = $this->razaService->listarRazas($request->query());

        return response()->json([
            'message' => 'Listado de razas obtenido correctamente.',
            'data'    => $razas,
        ], 200);
    }

    // GET /api/razas/{id}
    public function show(int $id): JsonResponse
    {
        $raza = $this->razaService->obtenerPorId($id);

        return response()->json([
            'message' => 'Raza obtenida correctamente.',
            'data'    => $raza,
        ], 200);
    }

    // POST /api/razas
    public function store(StoreRazaRequest $request): JsonResponse
    {
        $raza = $this->razaService->crearRaza($request->validated());

        return response()->json([
            'message' => 'Raza creada correctamente.',
            'data'    => $raza,
        ], 201);
    }

    // PUT/PATCH /api/razas/{id}
    public function update(UpdateRazaRequest $request, int $id): JsonResponse
    {
        $raza = $this->razaService->actualizarRaza($id, $request->validated());

        return response()->json([
            'message' => 'Raza actualizada correctamente.',
            'data'    => $raza,
        ], 200);
    }

    // DELETE /api/razas/{id}
    public function destroy($id): JsonResponse
    {
        $this->razaService->eliminarRaza((int) $id);

        return response()->json([
            'message' => 'Raza eliminada correctamente.',
        ], 200);
    }
}
