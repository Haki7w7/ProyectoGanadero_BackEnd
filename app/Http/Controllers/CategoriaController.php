<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoriaRequest;
use App\Http\Requests\UpdateCategoriaRequest;
use App\Services\CategoriaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CategoriaController extends Controller
{
    public function __construct(private readonly CategoriaService $categoriaService)
    {
    }

    // GET /api/categorias
    public function index(Request $request): JsonResponse
    {
        $categorias = $this->categoriaService->listarCategorias($request->query());

        return $this->respuestaPaginada($categorias, 'Listado de categorías obtenido correctamente.');
    }

    // GET /api/categorias/{id}
    public function show(int $id): JsonResponse
    {
        $categoria = $this->categoriaService->obtenerPorId($id);

        return response()->json([
            'message' => 'Categoría obtenida correctamente.',
            'data'    => $categoria,
        ], 200);
    }

    // POST /api/categorias
    public function store(StoreCategoriaRequest $request): JsonResponse
    {
        $categoria = $this->categoriaService->crearCategoria($request->validated());

        return $this->respuestaCreada($categoria, 'Categoría creada correctamente.', 'categorias');
    }

    // PUT/PATCH /api/categorias/{id}
    public function update(UpdateCategoriaRequest $request, int $id): JsonResponse
    {
        $categoria = $this->categoriaService->actualizarCategoria($id, $request->validated());

        return response()->json([
            'message' => 'Categoría actualizada correctamente.',
            'data'    => $categoria,
        ], 200);
    }

    // DELETE /api/categorias/{id}
    public function destroy($id): Response
    {
        $this->categoriaService->eliminarCategoria((int) $id);

        return $this->respuestaSinContenido();
    }
}
