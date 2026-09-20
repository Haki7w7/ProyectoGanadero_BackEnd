<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePesajeAnimalRequest;
use App\Http\Requests\StorePesajeRequest;
use App\Http\Requests\UpdatePesajeRequest;
use App\Http\Resources\PesajeResource;
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

    // GET /api/v1/pesajes
    public function index(Request $request): JsonResponse
    {
        $pesajes = $this->pesajeService->listarPesajes($request->query());

        return $this->respuestaPaginada($pesajes, 'Listado de pesajes obtenido correctamente.', PesajeResource::class);
    }

    // GET /api/v1/animales/{animal}/pesajes
    public function indexPorAnimal(Request $request, int $animal): JsonResponse
    {
        $this->animalService->verificarExistencia($animal);

        // El animal de la URL siempre manda sobre cualquier ?id_animal= de la query.
        $filtros = array_merge($request->query(), ['id_animal' => $animal]);
        $pesajes = $this->pesajeService->listarPesajes($filtros);

        return $this->respuestaPaginada($pesajes, 'Listado de pesajes del animal obtenido correctamente.', PesajeResource::class);
    }

    // GET /api/v1/pesajes/{pesaje}
    public function show(int $id): JsonResponse
    {
        $pesaje = $this->pesajeService->obtenerPorId($id);

        return $this->respuestaOk(new PesajeResource($pesaje), 'Pesaje obtenido correctamente.');
    }

    // POST /api/v1/pesajes
    public function store(StorePesajeRequest $request): JsonResponse
    {
        $pesaje = $this->pesajeService->crearPesaje($request->validated());

        return $this->respuestaCreada($pesaje, 'Pesaje registrado correctamente.', 'pesajes', new PesajeResource($pesaje));
    }

    // POST /api/v1/animales/{animal}/pesajes
    public function storePorAnimal(StorePesajeAnimalRequest $request, int $animal): JsonResponse
    {
        // StorePesajeAnimalRequest ya inyectó id_animal desde la URL.
        $pesaje = $this->pesajeService->crearPesaje($request->validated());

        // El recurso creado vive en la ruta plana /api/v1/pesajes/{id}.
        return $this->respuestaCreada($pesaje, 'Pesaje registrado correctamente.', 'pesajes', new PesajeResource($pesaje));
    }

    // PUT/PATCH /api/v1/pesajes/{pesaje}
    public function update(UpdatePesajeRequest $request, int $id): JsonResponse
    {
        $pesaje = $this->pesajeService->actualizarPesaje($id, $request->validated());

        return $this->respuestaOk(new PesajeResource($pesaje), 'Pesaje actualizado correctamente.');
    }

    // DELETE /api/v1/pesajes/{pesaje}
    public function destroy($id): Response
    {
        $this->pesajeService->eliminarPesaje((int) $id);

        return $this->respuestaSinContenido();
    }
}
