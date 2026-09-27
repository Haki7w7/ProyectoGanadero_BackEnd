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

    /**
     * Listar aplicaciones de tratamiento.
     *
     * Retorna el listado paginado de aplicaciones de tratamientos veterinarios administradas a los animales.
     */
    public function index(Request $request): JsonResponse
    {
        $registros = $this->tratamientoAnimalService->listarTratamientoAnimal($request->query());

        return $this->respuestaPaginada($registros, 'Listado de aplicaciones de tratamiento obtenido correctamente.', TratamientoAnimalResource::class);
    }

    /**
     * Historial de tratamientos de un animal.
     *
     * Retorna el historial sanitario completo y los tratamientos aplicados a un animal específico.
     */
    public function indexPorAnimal(Request $request, int $animal): JsonResponse
    {
        $this->animalService->verificarExistencia($animal);

        // El animal de la URL siempre manda sobre cualquier ?id_animal= de la query.
        $filtros = array_merge($request->query(), ['id_animal' => $animal]);
        $registros = $this->tratamientoAnimalService->listarTratamientoAnimal($filtros);

        return $this->respuestaPaginada($registros, 'Historial sanitario del animal obtenido correctamente.', TratamientoAnimalResource::class);
    }

    /**
     * Obtener detalle de una aplicación de tratamiento.
     *
     * Retorna la información detallada de una aplicación de tratamiento por su ID.
     */
    public function show(int $id): JsonResponse
    {
        $registro = $this->tratamientoAnimalService->obtenerPorId($id);

        return $this->respuestaOk(new TratamientoAnimalResource($registro), 'Aplicación de tratamiento obtenida correctamente.');
    }

    /**
     * Registrar aplicación de tratamiento.
     *
     * Registra la administración de un tratamiento a un animal con verificación de insumos y dosis.
     */
    public function store(StoreTratamientoAnimalRequest $request): JsonResponse
    {
        $registro = $this->tratamientoAnimalService->crearTratamientoAnimal($request->validated());

        return $this->respuestaCreada($registro, 'Aplicación de tratamiento registrada correctamente.', 'tratamientos-aplicaciones', new TratamientoAnimalResource($registro));
    }

    /**
     * Actualizar aplicación de tratamiento.
     *
     * Actualiza la información de una aplicación de tratamiento existente por su ID.
     */
    public function update(UpdateTratamientoAnimalRequest $request, int $id): JsonResponse
    {
        $registro = $this->tratamientoAnimalService->actualizarTratamientoAnimal($id, $request->validated());

        return $this->respuestaOk(new TratamientoAnimalResource($registro), 'Aplicación de tratamiento actualizada correctamente.');
    }

    /**
     * Eliminar aplicación de tratamiento.
     *
     * Elimina del sistema el registro de la aplicación de tratamiento indicada.
     */
    public function destroy($id): Response
    {
        $this->tratamientoAnimalService->eliminarTratamientoAnimal((int) $id);

        return $this->respuestaSinContenido();
    }
}
