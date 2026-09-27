<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRazaRequest;
use App\Http\Requests\UpdateRazaRequest;
use App\Http\Resources\RazaResource;
use App\Services\RazaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RazaController extends Controller
{
    public function __construct(private readonly RazaService $razaService)
    {
    }

    /**
     * Listar razas.
     *
     * Retorna el listado paginado de razas registradas en el sistema.
     */
    public function index(Request $request): JsonResponse
    {
        $razas = $this->razaService->listarRazas($request->query());

        return $this->respuestaPaginada($razas, 'Listado de razas obtenido correctamente.', RazaResource::class);
    }

    /**
     * Obtener detalle de una raza.
     *
     * Retorna la información de una raza específica por su ID.
     */
    public function show(int $id): JsonResponse
    {
        $raza = $this->razaService->obtenerPorId($id);

        return $this->respuestaOk(new RazaResource($raza), 'Raza obtenida correctamente.');
    }

    /**
     * Registrar una nueva raza.
     *
     * Valida los datos y registra una nueva raza de ganado en el sistema.
     */
    public function store(StoreRazaRequest $request): JsonResponse
    {
        $raza = $this->razaService->crearRaza($request->validated());

        return $this->respuestaCreada($raza, 'Raza creada correctamente.', 'razas', new RazaResource($raza));
    }

    /**
     * Actualizar una raza.
     *
     * Actualiza la información de una raza existente por su ID.
     */
    public function update(UpdateRazaRequest $request, int $id): JsonResponse
    {
        $raza = $this->razaService->actualizarRaza($id, $request->validated());

        return $this->respuestaOk(new RazaResource($raza), 'Raza actualizada correctamente.');
    }

    /**
     * Eliminar una raza.
     *
     * Elimina del sistema la raza indicada por su ID.
     */
    public function destroy($id): Response
    {
        $this->razaService->eliminarRaza((int) $id);

        return $this->respuestaSinContenido();
    }
}
