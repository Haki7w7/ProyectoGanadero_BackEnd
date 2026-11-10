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

    /**
     * Listar rondas.
     *
     * Retorna las rondas de inspección. Operarios visualizan únicamente sus propias rondas; Administradores visualizan el historial global.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Ronda::class); // CAPA 1

        $rondas = $this->rondaService->listarRondas($request->user(), $request->query());

        return $this->respuestaPaginada($rondas, 'Listado de rondas obtenido correctamente.', RondaResource::class);
    }

    /**
     * Iniciar una nueva ronda.
     *
     * Crea e inicia una ronda de inspección para el operario autenticado en estado en_progreso.
     */
    public function store(StoreRondaRequest $request): JsonResponse
    {
        $this->authorize('create', Ronda::class); // CAPA 1

        $ronda = $this->rondaService->iniciarRonda($request->user(), $request->validated());

        return $this->respuestaCreada($ronda, 'Ronda de inspección iniciada correctamente.', 'rondas', new RondaResource($ronda));
    }

    /**
     * Obtener detalle de una ronda.
     *
     * Retorna la información completa de una ronda y todos sus puntos de potreros registrados.
     */
    public function show(int $id, Request $request): JsonResponse
    {
        $ronda = $this->rondaService->obtenerPorId($id);

        $this->authorize('view', $ronda); // CAPA 1

        return $this->respuestaOk(new RondaResource($ronda), 'Detalle de la ronda obtenido correctamente.');
    }

    /**
     * Registrar punto de control en la ronda.
     *
     * Registra un potrero inspeccionado mediante su código QR, coordenadas GPS y estado físico del terreno.
     */
    public function registrarPunto(StorePuntoRondaRequest $request, int $id): JsonResponse
    {
        $ronda = $this->rondaService->obtenerPorId($id);

        $this->authorize('update', $ronda); // CAPA 1

        $punto = $this->rondaService->registrarPunto($id, $request->validated(), $request->user());

        return $this->respuestaCreada($punto, 'Punto de control registrado en la ronda correctamente.', 'puntos_ronda', new PuntoRondaResource($punto));
    }

    /**
     * Finalizar ronda de inspección.
     *
     * Marca la ronda activa como completada y asigna la fecha y hora de cierre.
     */
    public function finalizar(FinalizarRondaRequest $request, int $id): JsonResponse
    {
        $ronda = $this->rondaService->obtenerPorId($id);

        $this->authorize('update', $ronda); // CAPA 1

        $ronda = $this->rondaService->finalizarRonda($id, $request->validated(), $request->user());

        return $this->respuestaOk(new RondaResource($ronda), 'Ronda de inspección finalizada exitosamente.');
    }
}

