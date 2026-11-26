<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTratamientoAnimalRequest;
use App\Http\Requests\UpdateTratamientoAnimalRequest;
use App\Http\Resources\TratamientoAnimalResource;
use App\Models\TratamientoAnimal;
use App\Services\AnimalService;
use App\Services\TratamientoAnimalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TratamientoAnimalController extends Controller
{
    public function __construct(
        private readonly TratamientoAnimalService $tratamientoAnimalService,
        private readonly AnimalService $animalService,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', TratamientoAnimal::class);

        $registros = $this->tratamientoAnimalService->listarTratamientoAnimal($request->query());

        return $this->respuestaPaginada($registros, 'Listado de aplicaciones de tratamiento obtenido correctamente.', TratamientoAnimalResource::class);
    }

    public function indexPorAnimal(Request $request, int $animal): JsonResponse
    {
        $this->authorize('viewAny', TratamientoAnimal::class);

        $this->animalService->verificarExistencia($animal);

        $filtros = array_merge($request->query(), ['id_animal' => $animal]);
        $registros = $this->tratamientoAnimalService->listarTratamientoAnimal($filtros);

        return $this->respuestaPaginada($registros, 'Historial sanitario del animal obtenido correctamente.', TratamientoAnimalResource::class);
    }

    public function show(int $id): JsonResponse
    {
        $registro = $this->tratamientoAnimalService->obtenerPorId($id);

        $this->authorize('view', $registro);

        return $this->respuestaOk(new TratamientoAnimalResource($registro), 'Aplicación de tratamiento obtenida correctamente.');
    }

    public function store(StoreTratamientoAnimalRequest $request): JsonResponse
    {
        $this->authorize('create', TratamientoAnimal::class);

        $registro = $this->tratamientoAnimalService->crearTratamientoAnimal($request->validated());

        return $this->respuestaCreada($registro, 'Aplicación de tratamiento registrada correctamente.', 'tratamientos-aplicaciones', new TratamientoAnimalResource($registro));
    }

    public function update(UpdateTratamientoAnimalRequest $request, int $id): JsonResponse
    {
        $registro = $this->tratamientoAnimalService->obtenerPorId($id);

        $this->authorize('update', $registro);

        $registro = $this->tratamientoAnimalService->actualizarTratamientoAnimal($id, $request->validated());

        return $this->respuestaOk(new TratamientoAnimalResource($registro), 'Aplicación de tratamiento actualizada correctamente.');
    }

    public function destroy(int $id): Response
    {
        $registro = $this->tratamientoAnimalService->obtenerPorId($id);

        $this->authorize('delete', $registro);

        $this->tratamientoAnimalService->eliminarTratamientoAnimal($id);

        return $this->respuestaSinContenido();
    }
}
