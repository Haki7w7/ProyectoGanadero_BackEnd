<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTratamientoAnimalRequest;
use App\Http\Requests\UpdateTratamientoAnimalRequest;
use App\Services\TratamientoAnimalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TratamientoAnimalController extends Controller
{
    public function __construct(private readonly TratamientoAnimalService $tratamientoAnimalService)
    {
    }

    // GET /api/tratamiento-animal
    public function index(Request $request): JsonResponse
    {
        $registros = $this->tratamientoAnimalService->listarTratamientoAnimal($request->query());

        return response()->json([
            'message' => 'Listado de aplicaciones de tratamiento obtenido correctamente.',
            'data'    => $registros,
        ], 200);
    }

    // GET /api/tratamiento-animal/{id}
    public function show(int $id): JsonResponse
    {
        $registro = $this->tratamientoAnimalService->obtenerPorId($id);

        return response()->json([
            'message' => 'Aplicación de tratamiento obtenida correctamente.',
            'data'    => $registro,
        ], 200);
    }

    // POST /api/tratamiento-animal
    public function store(StoreTratamientoAnimalRequest $request): JsonResponse
    {
        $registro = $this->tratamientoAnimalService->crearTratamientoAnimal($request->validated());

        return response()->json([
            'message' => 'Aplicación de tratamiento registrada correctamente.',
            'data'    => $registro,
        ], 201);
    }

    // PUT/PATCH /api/tratamiento-animal/{id}
    public function update(UpdateTratamientoAnimalRequest $request, int $id): JsonResponse
    {
        $registro = $this->tratamientoAnimalService->actualizarTratamientoAnimal($id, $request->validated());

        return response()->json([
            'message' => 'Aplicación de tratamiento actualizada correctamente.',
            'data'    => $registro,
        ], 200);
    }

    // DELETE /api/tratamiento-animal/{id}
    public function destroy($id): JsonResponse
    {
        $this->tratamientoAnimalService->eliminarTratamientoAnimal((int) $id);

        return response()->json([
            'message' => 'Aplicación de tratamiento eliminada correctamente.',
        ], 200);
    }
}
