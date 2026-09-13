<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAnimalRequest;
use App\Http\Requests\UpdateAnimalRequest;
use App\Models\Animal;
use App\Services\AnimalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AnimalController extends Controller
{

    public function __construct(private readonly AnimalService $animalService)
    {
        /* Aplicar middleware de autenticación a todas las rutas excepto index y show
        $this->middleware('auth:sanctum')->except(['index', 'show']);*/
    }

    
    // GET /api/animales
    public function index(Request $request): JsonResponse
    {
        $animales = $this->animalService->listarAnimales($request->query());
        return response()->json([
            'message' => 'Listado de animales obtenido correctamente.',
            'data'    => $animales,
        ], 200);
    }

    // GET /api/animales/{id_animal}
    public function show(int $id): JsonResponse
    {
        $animal = $this->animalService->obtenerPorId($id);
        return response()->json([
            'message' => 'Animal obtenido correctamente.',
            'data'    => $animal,
        ], 200);
    }

    // POST /api/animales
    public function store(StoreAnimalRequest $request)
    {
      $animal = $this->animalService->crearAnimal($request->validated());

        return response()->json([
            'message' => 'Animal creado correctamente.',
            'data'    => $animal,
        ], 201);
    }

    // PUT/PATCH /api/animales/{animal}
    public function update(UpdateAnimalRequest $request,int $id):JsonResponse
    {
        $animal = $this->animalService->actualizarAnimal($id, $request->validated());

        return response()->json([
            'message' => 'Animal actualizado correctamente.',
            'data'    => $animal,
        ], 200);
    }

    // DELETE /api/animales/{animal}
    public function destroy($id): JsonResponse
    {
        $this->animalService->eliminarAnimal($id);

        return response()->json([
            'message' => 'Animal eliminado correctamente.',
        ], 200);
    }
}
