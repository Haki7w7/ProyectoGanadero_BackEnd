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

    /**
     * Listar pesajes.
     *
     * Retorna el listado paginado de registros de pesaje con soporte de filtros.
     */
    public function index(Request $request): JsonResponse
    {
        $pesajes = $this->pesajeService->listarPesajes($request->query());

        return $this->respuestaPaginada($pesajes, 'Listado de pesajes obtenido correctamente.', PesajeResource::class);
    }

    /**
     * Listar pesajes de un animal.
     *
     * Retorna el historial cronológico de pesajes asociados a un animal específico.
     */
    public function indexPorAnimal(Request $request, int $animal): JsonResponse
    {
        $this->animalService->verificarExistencia($animal);

        // El animal de la URL siempre manda sobre cualquier ?id_animal= de la query.
        $filtros = array_merge($request->query(), ['id_animal' => $animal]);
        $pesajes = $this->pesajeService->listarPesajes($filtros);

        return $this->respuestaPaginada($pesajes, 'Listado de pesajes del animal obtenido correctamente.', PesajeResource::class);
    }

    /**
     * Obtener detalle de un pesaje.
     *
     * Retorna la información completa de un registro de pesaje por su ID.
     */
    public function show(int $id): JsonResponse
    {
        $pesaje = $this->pesajeService->obtenerPorId($id);

        return $this->respuestaOk(new PesajeResource($pesaje), 'Pesaje obtenido correctamente.');
    }

    /**
     * Registrar un nuevo pesaje.
     *
     * Valida los datos y registra un nuevo pesaje en el sistema.
     */
    public function store(StorePesajeRequest $request): JsonResponse
    {
        $pesaje = $this->pesajeService->crearPesaje($request->validated());

        return $this->respuestaCreada($pesaje, 'Pesaje registrado correctamente.', 'pesajes', new PesajeResource($pesaje));
    }

    /**
     * Registrar pesaje para un animal.
     *
     * Registra un nuevo pesaje vinculado directamente al animal indicado en la ruta.
     */
    public function storePorAnimal(StorePesajeAnimalRequest $request, int $animal): JsonResponse
    {
        // StorePesajeAnimalRequest ya inyectó id_animal desde la URL.
        $pesaje = $this->pesajeService->crearPesaje($request->validated());

        // El recurso creado vive en la ruta plana /api/v1/pesajes/{id}.
        return $this->respuestaCreada($pesaje, 'Pesaje registrado correctamente.', 'pesajes', new PesajeResource($pesaje));
    }

    /**
     * Actualizar un pesaje.
     *
     * Actualiza los datos de un registro de pesaje existente por su ID.
     */
    public function update(UpdatePesajeRequest $request, int $id): JsonResponse
    {
        $pesaje = $this->pesajeService->actualizarPesaje($id, $request->validated());

        return $this->respuestaOk(new PesajeResource($pesaje), 'Pesaje actualizado correctamente.');
    }

    /**
     * Eliminar un pesaje.
     *
     * Elimina el registro de pesaje indicado del sistema.
     */
    public function destroy($id): Response
    {
        $this->pesajeService->eliminarPesaje((int) $id);

        return $this->respuestaSinContenido();
    }
}
