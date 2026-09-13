<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePotreroRequest;
use App\Http\Requests\UpdatePotreroRequest;
use App\Models\Potrero;
use App\Services\PotreroService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PotreroController extends Controller
{

public function __construct(private readonly PotreroService $potreroService)
    {
        /* Aplicar middleware de autenticación a todas las rutas excepto index y show
        $this->middleware('auth:sanctum')->except(['index', 'show']);*/
    }
    

    // GET /api/potreros
    public function index(Request $request):JsonResponse
    {
        $potreros = $this->potreroService->listarPotreros($request->query());

        return response()->json([
            'message' => 'Listado de potreros obtenido correctamente.',
            'data'    => $potreros,
        ], 200);
    }

    // GET /api/potreros/{id_potrero}
    public function show(int $id):JsonResponse
    {
        $potrero = $this->potreroService->obtenerPorId($id);

        return response()->json([
            'message' => 'Potrero obtenido correctamente.',
            'data'    => $potrero,
        ], 200);
    }

    // POST /api/potreros
    public function store(StorePotreroRequest $request):JsonResponse
    {
      $potrero = $this->potreroService->crearPotrero($request->validated());

        return response()->json([
            'message' => 'Potrero creado correctamente.',
            'data'    => $potrero,
        ], 201);
    }

    // PUT/PATCH /api/potreros/{potrero}
    public function update(UpdatePotreroRequest $request, int $id):JsonResponse
    {
        $potrero = $this->potreroService->actualizarPotrero($id, $request->validated());

        return response()->json([
            'message' => 'Potrero actualizado correctamente.',
            'data'    => $potrero,
        ], 200);
    }

    // DELETE /api/potreros/{potrero}
    public function destroy(int $id):JsonResponse
    {
        $potrero = $this->potreroService->eliminarPotrero($id);

        return response()->json([
            'message' => 'Potrero eliminado correctamente.',
        ], 200);
    }
}
