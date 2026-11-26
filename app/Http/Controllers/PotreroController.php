<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePotreroRequest;
use App\Http\Requests\UpdatePotreroRequest;
use App\Http\Resources\PotreroResource;
use App\Models\Potrero;
use App\Services\PotreroService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PotreroController extends Controller
{
    public function __construct(private readonly PotreroService $potreroService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Potrero::class);

        $potreros = $this->potreroService->listarPotreros($request->query());

        return $this->respuestaPaginada($potreros, 'Listado de potreros obtenido correctamente.', PotreroResource::class);
    }

    public function show(int $id): JsonResponse
    {
        $potrero = $this->potreroService->obtenerPorId($id);

        $this->authorize('view', $potrero);

        return $this->respuestaOk(new PotreroResource($potrero), 'Potrero obtenido correctamente.');
    }

    public function store(StorePotreroRequest $request): JsonResponse
    {
        $this->authorize('create', Potrero::class);

        $potrero = $this->potreroService->crearPotrero($request->validated());

        return $this->respuestaCreada($potrero, 'Potrero creado correctamente.', 'potreros', new PotreroResource($potrero));
    }

    public function update(UpdatePotreroRequest $request, int $id): JsonResponse
    {
        $potrero = $this->potreroService->obtenerPorId($id);

        $this->authorize('update', $potrero);

        $potrero = $this->potreroService->actualizarPotrero($id, $request->validated());

        return $this->respuestaOk(new PotreroResource($potrero), 'Potrero actualizado correctamente.');
    }

    public function destroy(int $id): Response
    {
        $potrero = $this->potreroService->obtenerPorId($id);

        $this->authorize('delete', $potrero);

        $this->potreroService->eliminarPotrero($id);

        return $this->respuestaSinContenido();
    }
}
