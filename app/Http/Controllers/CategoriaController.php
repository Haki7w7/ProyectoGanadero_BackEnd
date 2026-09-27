<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoriaRequest;
use App\Http\Requests\UpdateCategoriaRequest;
use App\Http\Resources\CategoriaResource;
use App\Services\CategoriaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CategoriaController extends Controller
{
    public function __construct(private readonly CategoriaService $categoriaService)
    {
    }

    /**
     * Listar categorías.
     *
     * Retorna el listado paginado de categorías registradas en el sistema.
     */
    public function index(Request $request): JsonResponse
    {
        $categorias = $this->categoriaService->listarCategorias($request->query());

        return $this->respuestaPaginada($categorias, 'Listado de categorías obtenido correctamente.', CategoriaResource::class);
    }

    /**
     * Obtener detalle de una categoría.
     *
     * Retorna la información de una categoría específica por su ID.
     */
    public function show(int $id): JsonResponse
    {
        $categoria = $this->categoriaService->obtenerPorId($id);

        return $this->respuestaOk(new CategoriaResource($categoria), 'Categoría obtenida correctamente.');
    }

    /**
     * Registrar una nueva categoría.
     *
     * Valida los datos y registra una nueva categoría en el sistema.
     */
    public function store(StoreCategoriaRequest $request): JsonResponse
    {
        $categoria = $this->categoriaService->crearCategoria($request->validated());

        return $this->respuestaCreada($categoria, 'Categoría creada correctamente.', 'categorias', new CategoriaResource($categoria));
    }

    /**
     * Actualizar una categoría.
     *
     * Actualiza la información de una categoría existente por su ID.
     */
    public function update(UpdateCategoriaRequest $request, int $id): JsonResponse
    {
        $categoria = $this->categoriaService->actualizarCategoria($id, $request->validated());

        return $this->respuestaOk(new CategoriaResource($categoria), 'Categoría actualizada correctamente.');
    }

    /**
     * Eliminar una categoría.
     *
     * Elimina del sistema la categoría indicada por su ID.
     */
    public function destroy($id): Response
    {
        $this->categoriaService->eliminarCategoria((int) $id);

        return $this->respuestaSinContenido();
    }
}
