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

    // GET /api/v1/tratamientos
    public function index(Request $request): JsonResponse
    {
        $tratamientos = $this->tratamientoService->listarTratamientos($request->query());

        return $this->respuestaPaginada($tratamientos, 'Listado de tratamientos obtenido correctamente.', TratamientoResource::class);
    }

    // GET /api/v1/tratamientos/{tratamiento}
    public function show(int $id): JsonResponse
    {
        $tratamiento = $this->tratamientoService->obtenerPorId($id);

        return $this->respuestaOk(new TratamientoResource($tratamiento), 'Tratamiento obtenido correctamente.');
    }

    // POST /api/v1/tratamientos
    public function store(StoreTratamientoRequest $request): JsonResponse
    {
        $tratamiento = $this->tratamientoService->crearTratamiento($request->validated());

        return $this->respuestaCreada($tratamiento, 'Tratamiento creado correctamente.', 'tratamientos', new TratamientoResource($tratamiento));
    }

    // PUT/PATCH /api/v1/tratamientos/{tratamiento}
    public function update(UpdateTratamientoRequest $request, int $id): JsonResponse
    {
        $tratamiento = $this->tratamientoService->actualizarTratamiento($id, $request->validated());

        return $this->respuestaOk(new TratamientoResource($tratamiento), 'Tratamiento actualizado correctamente.');
    }

    // DELETE /api/v1/tratamientos/{tratamiento}
    public function destroy($id): Response
    {
        $this->tratamientoService->eliminarTratamiento((int) $id);

        return $this->respuestaSinContenido();
    }
}
