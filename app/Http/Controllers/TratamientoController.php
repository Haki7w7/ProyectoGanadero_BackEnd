<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTratamientoRequest;
use App\Http\Requests\UpdateTratamientoRequest;
use App\Services\TratamientoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TratamientoController extends Controller
{
    public function __construct(private readonly TratamientoService $tratamientoService)
    {
    }

    // GET /api/tratamientos
    public function index(Request $request): JsonResponse
    {
        $tratamientos = $this->tratamientoService->listarTratamientos($request->query());

        return response()->json([
            'message' => 'Listado de tratamientos obtenido correctamente.',
            'data'    => $tratamientos,
        ], 200);
    }

    // GET /api/tratamientos/{id}
    public function show(int $id): JsonResponse
    {
        $tratamiento = $this->tratamientoService->obtenerPorId($id);

        return response()->json([
            'message' => 'Tratamiento obtenido correctamente.',
            'data'    => $tratamiento,
        ], 200);
    }

    // POST /api/tratamientos
    public function store(StoreTratamientoRequest $request): JsonResponse
    {
        $tratamiento = $this->tratamientoService->crearTratamiento($request->validated());

        return response()->json([
            'message' => 'Tratamiento creado correctamente.',
            'data'    => $tratamiento,
        ], 201);
    }

    // PUT/PATCH /api/tratamientos/{id}
    public function update(UpdateTratamientoRequest $request, int $id): JsonResponse
    {
        $tratamiento = $this->tratamientoService->actualizarTratamiento($id, $request->validated());

        return response()->json([
            'message' => 'Tratamiento actualizado correctamente.',
            'data'    => $tratamiento,
        ], 200);
    }

    // DELETE /api/tratamientos/{id}
    public function destroy($id): JsonResponse
    {
        $this->tratamientoService->eliminarTratamiento((int) $id);

        return response()->json([
            'message' => 'Tratamiento eliminado correctamente.',
        ], 200);
    }
}
