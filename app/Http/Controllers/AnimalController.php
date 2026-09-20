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


    // GET /api/v1/animales
    public function index(Request $request): AnimalCollection
    {
        $animales = $this->animalService->listarAnimales($request->query());

        // AnimalCollection ya entrega data + meta + links; se agrega el mensaje.
        return (new AnimalCollection($animales))
            ->additional(['message' => 'Listado de animales obtenido correctamente.']);
    }

    // GET /api/v1/animales/{animal}
    public function show(int $id): JsonResponse
    {
        $animal = $this->animalService->obtenerPorId($id);

        return $this->respuestaOk(new AnimalResource($animal), 'Animal obtenido correctamente.');
    }

    // POST /api/v1/animales
    public function store(StoreAnimalRequest $request): JsonResponse
    {
        $animal = $this->animalService->crearAnimal($request->validated());

        return $this->respuestaCreada($animal, 'Animal creado correctamente.', 'animales', new AnimalResource($animal));
    }

    // PUT/PATCH /api/v1/animales/{animal}
    public function update(UpdateAnimalRequest $request,int $id):JsonResponse
    {
        $animal = $this->animalService->actualizarAnimal($id, $request->validated());

        return $this->respuestaOk(new AnimalResource($animal), 'Animal actualizado correctamente.');
    }

    // GET /api/v1/potreros/{potrero}/animales
    public function indexPorPotrero(Request $request, int $potrero): JsonResponse
    {
        $this->potreroService->obtenerPorId($potrero);

        $animales = $this->animalService->listarAnimales(array_merge($request->query(), ['potrero_id' => $potrero]));

        return $this->respuestaPaginada($animales, 'Listado de animales del potrero obtenido correctamente.', AnimalResource::class);
    }

    // GET /api/v1/razas/{raza}/animales
    public function indexPorRaza(Request $request, int $raza): JsonResponse
    {
        $this->razaService->obtenerPorId($raza);

        $animales = $this->animalService->listarAnimales(array_merge($request->query(), ['raza_id' => $raza]));

        return $this->respuestaPaginada($animales, 'Listado de animales de la raza obtenido correctamente.', AnimalResource::class);
    }

    // DELETE /api/v1/animales/{animal}
    public function destroy($id): Response
    {
        $this->animalService->eliminarAnimal($id);

        return $this->respuestaSinContenido();
    }
}
