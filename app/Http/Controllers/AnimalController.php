<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAnimalRequest;
use App\Http\Requests\UpdateAnimalRequest;
use App\Http\Resources\AnimalCollection;
use App\Http\Resources\AnimalResource;
use App\Models\Animal;
use App\Services\AnimalService;
use App\Services\PotreroService;
use App\Services\RazaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;



class AnimalController extends Controller
{
    public function __construct(
        private readonly AnimalService $animalService,
        private readonly PotreroService $potreroService,
        private readonly RazaService $razaService,
    ) {
        /* Aplicar middleware de autenticación a todas las rutas excepto index y show
        $this->middleware('auth:sanctum')->except(['index', 'show']);*/
    }

    /**
     * Listar animales.
     *
     * Retorna el listado paginado de animales registrados en el sistema con soporte de filtros y ordenamiento.
     *
     * @param  Request  $request  Petición HTTP con posibles parámetros de filtro (raza_id, potrero_id, sexo, estado) y paginación.
     * @return AnimalCollection Colección de animales con datos paginados, meta y links.
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
     * Retorna la información completa de un animal específico por su ID,
     * incluyendo sus relaciones con raza, potrero, historial de pesajes y tratamientos.
     *
     * @param  int  $id  Identificador único del animal.
     * @return JsonResponse Detalle del animal en formato JSON.
     */
    public function show(int $id): JsonResponse
    {
        $animal = $this->animalService->obtenerPorId($id);

        return $this->respuestaOk(new AnimalResource($animal), 'Animal obtenido correctamente.');
    }

    /**
     * Registrar un nuevo animal.
     *
     * Valida los datos recibidos y registra un nuevo animal en el sistema.
     * Valida la capacidad máxima del potrero asignado antes de completar el registro.
     *
     * @param  StoreAnimalRequest  $request  Petición con los datos validados del animal.
     * @return JsonResponse Recurso AnimalResource creado (201 Created) con cabecera Location.
     */
    public function store(StoreAnimalRequest $request): JsonResponse
    {
        $animal = $this->animalService->crearAnimal($request->validated());

        return $this->respuestaCreada($animal, 'Animal creado correctamente.', 'animales', new AnimalResource($animal));
    }

    /**
     * Actualizar datos de un animal.
     *
     * Actualiza la información de un animal existente por su ID.
     * Si se modifica la asignación de potrero, valida que el nuevo potrero tenga capacidad disponible.
     *
     * @param  UpdateAnimalRequest  $request  Petición con los campos validados para la actualización.
     * @param  int  $id  Identificador único del animal a actualizar.
     * @return JsonResponse Recurso AnimalResource actualizado (200 OK).
     */
    public function update(UpdateAnimalRequest $request, int $id): JsonResponse
    {
        $animal = $this->animalService->actualizarAnimal($id, $request->validated());

        return $this->respuestaOk(new AnimalResource($animal), 'Animal actualizado correctamente.');
    }

    /**
     * Listar animales por potrero.
     *
     * Retorna el listado paginado de animales asignados a un potrero específico.
     * Valida previamente la existencia del potrero indicado en la ruta.
     *
     * @param  Request  $request  Petición HTTP con parámetros de filtrado y paginación.
     * @param  int  $potrero  Identificador único del potrero.
     * @return JsonResponse Listado paginado de animales del potrero (200 OK).
     */
    public function indexPorPotrero(Request $request, int $potrero): JsonResponse
    {
        $this->potreroService->obtenerPorId($potrero);

        $animales = $this->animalService->listarAnimales(array_merge($request->query(), ['potrero_id' => $potrero]));

        return $this->respuestaPaginada($animales, 'Listado de animales del potrero obtenido correctamente.', AnimalResource::class);
    }

    /**
     * Listar animales por raza.
     *
     * Retorna el listado paginado de animales pertenecientes a una raza específica.
     * Valida previamente la existencia de la raza indicada en la ruta.
     *
     * @param  Request  $request  Petición HTTP con parámetros de filtrado y paginación.
     * @param  int  $raza  Identificador único de la raza.
     * @return JsonResponse Listado paginado de animales de la raza (200 OK).
     */
    public function indexPorRaza(Request $request, int $raza): JsonResponse
    {
        $this->razaService->obtenerPorId($raza);

        $animales = $this->animalService->listarAnimales(array_merge($request->query(), ['raza_id' => $raza]));

        return $this->respuestaPaginada($animales, 'Listado de animales de la raza obtenido correctamente.', AnimalResource::class);
    }

    /**
     * Eliminar un animal.
     *
     * Elimina el registro de un animal del sistema por su ID.
     * Impide la eliminación si el animal posee registros de pesaje en su historial.
     *
     * @param  int|string  $id  Identificador único del animal a eliminar.
     * @return Response Respuesta HTTP 204 No Content sin cuerpo.
     */
 public function destroy(Request $request, $id): Response
{
    // 1. Verificación de permisos
    if (! $request->user()->tokenCan('animales:delete') && ! $request->user()->tokenCan('*')) {
        abort(403, 'Acceso denegado. No posee permisos suficientes');
    }

    // 2. Lógica de negocio en el servicio
    $this->animalService->eliminarAnimal($id);

    // 3. Respuesta exitosa (204 No Content)
    return $this->respuestaSinContenido();
}
}
