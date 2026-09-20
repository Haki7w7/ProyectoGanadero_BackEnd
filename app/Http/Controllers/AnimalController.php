<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAnimalRequest;
use App\Http\Requests\UpdateAnimalRequest;
use App\Http\Resources\AnimalCollection;
use App\Http\Resources\AnimalResource;
use App\Models\Animal;
use App\Services\AnimalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * @tags Animales
 */
class AnimalController extends Controller
{
    public function __construct(private readonly AnimalService $animalService)
    {
    }

    /**
     * Listar animales.
     *
     * Retorna el listado paginado de animales con soporte de filtros por raza, potrero, sexo o estado, y ordenamiento dinámico.
     *
     * @response 200 { "message": "Listado de animales obtenido correctamente.", "data": {...} }
     */
    public function index(Request $request): AnimalCollection
    {
        $animales = $this->animalService->listarAnimales($request->query());

        // AnimalCollection ya entrega data + meta + links; se agrega el mensaje.
        return (new AnimalCollection($animales))
            ->additional(['message' => 'Listado de animales obtenido correctamente.']);
    }

    /**
     * Obtener detalle de un animal.
     *
     * Retorna la información completa de un animal por su ID, incluyendo raza, potrero, pesajes y tratamientos.
     *
     * @response 200 { "message": "Animal obtenido correctamente.", "data": {...} }
     * @response 404 { "error": "Recurso no encontrado", "mensaje": "El animal no existe." }
     */
    public function show(int $id): JsonResponse
    {
        $animal = $this->animalService->obtenerPorId($id);

        return $this->respuestaOk(new AnimalResource($animal), 'Animal obtenido correctamente.');
    }

    /**
     * Registrar un nuevo animal.
     *
     * Valida los datos y registra un nuevo animal, verificando la capacidad disponible del potrero seleccionado.
     *
     * @response 201 { "message": "Animal creado correctamente.", "data": {...} }
     * @response 422 { "message": "Los datos proporcionados no son válidos.", "errors": {...} }
     * @response 409 { "error": "Regla de Negocio", "mensaje": "El potrero seleccionado ha alcanzado su capacidad máxima." }
     */
    public function store(StoreAnimalRequest $request): JsonResponse
    {
        $animal = $this->animalService->crearAnimal($request->validated());

        return $this->respuestaCreada($animal, 'Animal creado correctamente.', 'animales', new AnimalResource($animal));
    }

    /**
     * Actualizar datos de un animal.
     *
     * Actualiza la información del animal especificado por su ID.
     *
     * @response 200 { "message": "Animal actualizado correctamente.", "data": {...} }
     * @response 404 { "error": "Recurso no encontrado" }
     * @response 422 { "message": "Los datos proporcionados no son válidos." }
     */
    public function update(UpdateAnimalRequest $request, int $id): JsonResponse
    {
        $animal = $this->animalService->actualizarAnimal($id, $request->validated());

        return $this->respuestaOk(new AnimalResource($animal), 'Animal actualizado correctamente.');
    }

    /**
     * Eliminar un animal.
     *
     * Elimina el animal del sistema. Falla si el animal tiene historial de pesajes registrados (RN-01).
     *
     * @response 200 { "message": "Animal eliminado correctamente." }
     * @response 404 { "error": "Recurso no encontrado" }
     * @response 409 { "error": "Regla de Negocio", "mensaje": "No se puede eliminar el animal porque tiene pesajes registrados." }
     */
    public function destroy($id): Response
    {
        $this->animalService->eliminarAnimal($id);

        return $this->respuestaSinContenido();
    }
}
