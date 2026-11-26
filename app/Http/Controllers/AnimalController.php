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

class AnimalController extends Controller
{
    public function __construct(
        private readonly AnimalService $animalService,
        private readonly PotreroService $potreroService,
        private readonly RazaService $razaService,
    ) {
    }

    public function index(Request $request): AnimalCollection
    {
        $this->authorize('viewAny', Animal::class);

        $animales = $this->animalService->listarAnimales($request->query());

        return (new AnimalCollection($animales))
            ->additional(['message' => 'Listado de animales obtenido correctamente.']);
    }

    public function show(int $id): JsonResponse
    {
        $animal = $this->animalService->obtenerPorId($id);

        $this->authorize('view', $animal);

        return $this->respuestaOk(new AnimalResource($animal), 'Animal obtenido correctamente.');
    }

    public function porQr(string $codigo_qr): JsonResponse
    {
        $animal = $this->animalService->obtenerPorCodigoQr($codigo_qr);

        $this->authorize('view', $animal);

        return $this->respuestaOk(new AnimalResource($animal), 'Animal obtenido correctamente mediante código QR.');
    }

    public function store(StoreAnimalRequest $request): JsonResponse
    {
        $this->authorize('create', Animal::class);

        $animal = $this->animalService->crearAnimal($request->validated());

        return $this->respuestaCreada($animal, 'Animal creado correctamente.', 'animales', new AnimalResource($animal));
    }

    public function update(UpdateAnimalRequest $request, int $id): JsonResponse
    {
        $animal = $this->animalService->obtenerPorId($id);

        $this->authorize('update', $animal);

        $animal = $this->animalService->actualizarAnimal($id, $request->validated());

        return $this->respuestaOk(new AnimalResource($animal), 'Animal actualizado correctamente.');
    }

    public function indexPorPotrero(Request $request, int $potrero): JsonResponse
    {
        $this->authorize('viewAny', Animal::class);

        $this->potreroService->obtenerPorId($potrero);

        $animales = $this->animalService->listarAnimales(array_merge($request->query(), ['potrero_id' => $potrero]));

        return $this->respuestaPaginada($animales, 'Listado de animales del potrero obtenido correctamente.', AnimalResource::class);
    }

    public function indexPorRaza(Request $request, int $raza): JsonResponse
    {
        $this->authorize('viewAny', Animal::class);

        $this->razaService->obtenerPorId($raza);

        $animales = $this->animalService->listarAnimales(array_merge($request->query(), ['raza_id' => $raza]));

        return $this->respuestaPaginada($animales, 'Listado de animales de la raza obtenido correctamente.', AnimalResource::class);
    }

    public function destroy(int $id): Response
    {
        $animal = $this->animalService->obtenerPorId($id);

        $this->authorize('delete', $animal);

        $this->animalService->eliminarAnimal($id);

        return $this->respuestaSinContenido();
    }
}
