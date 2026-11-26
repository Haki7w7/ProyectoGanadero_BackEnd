<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePesajeAnimalRequest;
use App\Http\Requests\StorePesajeRequest;
use App\Http\Requests\UpdatePesajeRequest;
use App\Http\Resources\PesajeResource;
use App\Models\Pesaje;
use App\Services\AnimalService;
use App\Services\PesajeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PesajeController extends Controller
{
    public function __construct(
        private readonly PesajeService $pesajeService,
        private readonly AnimalService $animalService,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Pesaje::class);

        $pesajes = $this->pesajeService->listarPesajes($request->query());

        return $this->respuestaPaginada($pesajes, 'Listado de pesajes obtenido correctamente.', PesajeResource::class);
    }

    public function indexPorAnimal(Request $request, int $animal): JsonResponse
    {
        $this->authorize('viewAny', Pesaje::class);

        $this->animalService->verificarExistencia($animal);

        $filtros = array_merge($request->query(), ['id_animal' => $animal]);
        $pesajes = $this->pesajeService->listarPesajes($filtros);

        return $this->respuestaPaginada($pesajes, 'Listado de pesajes del animal obtenido correctamente.', PesajeResource::class);
    }

    public function show(int $id): JsonResponse
    {
        $pesaje = $this->pesajeService->obtenerPorId($id);

        $this->authorize('view', $pesaje);

        return $this->respuestaOk(new PesajeResource($pesaje), 'Pesaje obtenido correctamente.');
    }

    public function store(StorePesajeRequest $request): JsonResponse
    {
        $this->authorize('create', Pesaje::class);

        $pesaje = $this->pesajeService->crearPesaje($request->validated());

        return $this->respuestaCreada($pesaje, 'Pesaje registrado correctamente.', 'pesajes', new PesajeResource($pesaje));
    }

    public function storePorAnimal(StorePesajeAnimalRequest $request, int $animal): JsonResponse
    {
        $this->authorize('create', Pesaje::class);

        $pesaje = $this->pesajeService->crearPesaje($request->validated());

        return $this->respuestaCreada($pesaje, 'Pesaje registrado correctamente.', 'pesajes', new PesajeResource($pesaje));
    }

    public function update(UpdatePesajeRequest $request, int $id): JsonResponse
    {
        $pesaje = $this->pesajeService->obtenerPorId($id);

        $this->authorize('update', $pesaje);

        $pesaje = $this->pesajeService->actualizarPesaje($id, $request->validated());

        return $this->respuestaOk(new PesajeResource($pesaje), 'Pesaje actualizado correctamente.');
    }

    public function destroy(int $id): Response
    {
        $pesaje = $this->pesajeService->obtenerPorId($id);

        $this->authorize('delete', $pesaje);

        $this->pesajeService->eliminarPesaje($id);

        return $this->respuestaSinContenido();
    }
}
