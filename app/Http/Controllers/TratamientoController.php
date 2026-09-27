<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTratamientoRequest;
use App\Http\Requests\UpdateTratamientoRequest;
use App\Http\Resources\TratamientoResource;
use App\Services\TratamientoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TratamientoController extends Controller
{
    public function __construct(private readonly TratamientoService $tratamientoService)
    {
    }

    /**
     * Listar tratamientos.
     *
     * Retorna el listado paginado del catálogo de tratamientos veterinarios disponibles.
     */
    public function index(Request $request): JsonResponse
    {
        $tratamientos = $this->tratamientoService->listarTratamientos($request->query());

        return $this->respuestaPaginada($tratamientos, 'Listado de tratamientos obtenido correctamente.', TratamientoResource::class);
    }

    /**
     * Obtener detalle de un tratamiento.
     *
     * Retorna la información de un tratamiento específico por su ID.
     */
    public function show(int $id): JsonResponse
    {
        $tratamiento = $this->tratamientoService->obtenerPorId($id);

        return $this->respuestaOk(new TratamientoResource($tratamiento), 'Tratamiento obtenido correctamente.');
    }

    /**
     * Registrar un nuevo tratamiento.
     *
     * Valida los datos y registra un nuevo tipo de tratamiento médico o preventivo.
     */
    public function store(StoreTratamientoRequest $request): JsonResponse
    {
        $tratamiento = $this->tratamientoService->crearTratamiento($request->validated());

        return $this->respuestaCreada($tratamiento, 'Tratamiento creado correctamente.', 'tratamientos', new TratamientoResource($tratamiento));
    }

    /**
     * Actualizar un tratamiento.
     *
     * Actualiza la información de un tratamiento existente por su ID.
     */
    public function update(UpdateTratamientoRequest $request, int $id): JsonResponse
    {
        $tratamiento = $this->tratamientoService->actualizarTratamiento($id, $request->validated());

        return $this->respuestaOk(new TratamientoResource($tratamiento), 'Tratamiento actualizado correctamente.');
    }

    /**
     * Eliminar un tratamiento.
     *
     * Elimina del catálogo el tratamiento indicado por su ID.
     */
    public function destroy($id): Response
    {
        $this->tratamientoService->eliminarTratamiento((int) $id);

        return $this->respuestaSinContenido();
    }
}
