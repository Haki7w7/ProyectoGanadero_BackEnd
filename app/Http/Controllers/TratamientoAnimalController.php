<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTratamientoAnimalRequest;
use App\Http\Requests\UpdateTratamientoAnimalRequest;
use App\Http\Resources\TratamientoAnimalResource;
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

    // GET /api/v1/tratamientos-aplicaciones
    public function index(Request $request): JsonResponse
    {
        $registros = $this->tratamientoAnimalService->listarTratamientoAnimal($request->query());

        return $this->respuestaPaginada($registros, 'Listado de aplicaciones de tratamiento obtenido correctamente.', TratamientoAnimalResource::class);
    }

    // GET /api/v1/animales/{animal}/tratamientos  (historial sanitario del animal)
    public function indexPorAnimal(Request $request, int $animal): JsonResponse
    {
        $this->animalService->verificarExistencia($animal);

        // El animal de la URL siempre manda sobre cualquier ?id_animal= de la query.
        $filtros = array_merge($request->query(), ['id_animal' => $animal]);
        $registros = $this->tratamientoAnimalService->listarTratamientoAnimal($filtros);

        return $this->respuestaPaginada($registros, 'Historial sanitario del animal obtenido correctamente.', TratamientoAnimalResource::class);
    }

    // GET /api/v1/tratamientos-aplicaciones/{tratamiento_animal}
    public function show(int $id): JsonResponse
    {
        $registro = $this->tratamientoAnimalService->obtenerPorId($id);

        return $this->respuestaOk(new TratamientoAnimalResource($registro), 'Aplicación de tratamiento obtenida correctamente.');
    }

    // POST /api/v1/tratamientos-aplicaciones
    public function store(StoreTratamientoAnimalRequest $request): JsonResponse
    {
        $registro = $this->tratamientoAnimalService->crearTratamientoAnimal($request->validated());

        return $this->respuestaCreada($registro, 'Aplicación de tratamiento registrada correctamente.', 'tratamientos-aplicaciones', new TratamientoAnimalResource($registro));
    }

    // PUT/PATCH /api/v1/tratamientos-aplicaciones/{tratamiento_animal}
    public function update(UpdateTratamientoAnimalRequest $request, int $id): JsonResponse
    {
        $registro = $this->tratamientoAnimalService->actualizarTratamientoAnimal($id, $request->validated());

        return $this->respuestaOk(new TratamientoAnimalResource($registro), 'Aplicación de tratamiento actualizada correctamente.');
    }

    // DELETE /api/v1/tratamientos-aplicaciones/{tratamiento_animal}
    public function destroy($id): Response
    {
        $this->tratamientoAnimalService->eliminarTratamientoAnimal((int) $id);

        return $this->respuestaSinContenido();
    }
}
