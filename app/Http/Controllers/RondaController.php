<?php

namespace App\Http\Controllers;

use App\Http\Requests\FinalizarRondaRequest;
use App\Http\Requests\StorePuntoRondaRequest;
use App\Http\Requests\StoreRondaRequest;
use App\Http\Resources\PuntoRondaResource;
use App\Http\Resources\RondaResource;
use App\Models\Ronda;
use App\Services\RondaService;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

#[Group('Rondas de Inspección')]
class RondaController extends Controller
{
    public function __construct(private readonly RondaService $rondaService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Ronda::class);

        $rondas = $this->rondaService->listarRondas($request->user(), $request->query());

        return $this->respuestaPaginada($rondas, 'Listado de rondas obtenido correctamente.', RondaResource::class);
    }

    public function store(StoreRondaRequest $request): JsonResponse
    {
        $this->authorize('create', Ronda::class);

        $ronda = $this->rondaService->iniciarRonda($request->user(), $request->validated());

        return $this->respuestaCreada($ronda, 'Ronda de inspección iniciada correctamente.', 'rondas', new RondaResource($ronda));
    }

    public function show(int $id, Request $request): JsonResponse
    {
        $ronda = $this->rondaService->obtenerPorId($id);

        $this->authorize('view', $ronda);

        return $this->respuestaOk(new RondaResource($ronda), 'Detalle de la ronda obtenido correctamente.');
    }

    public function registrarPunto(StorePuntoRondaRequest $request, int $id): JsonResponse
    {
        $ronda = $this->rondaService->obtenerPorId($id);

        $this->authorize('update', $ronda);

        $punto = $this->rondaService->registrarPunto($id, $request->validated(), $request->user());

        return $this->respuestaCreada($punto, 'Punto de control registrado en la ronda correctamente.', 'puntos_ronda', new PuntoRondaResource($punto));
    }

    public function finalizar(FinalizarRondaRequest $request, int $id): JsonResponse
    {
        $ronda = $this->rondaService->obtenerPorId($id);

        $this->authorize('update', $ronda);

        $ronda = $this->rondaService->finalizarRonda($id, $request->validated(), $request->user());

        return $this->respuestaOk(new RondaResource($ronda), 'Ronda de inspección finalizada exitosamente.');
    }
}

