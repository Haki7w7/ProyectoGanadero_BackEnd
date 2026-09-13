<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    // GET /api/categorias
    public function index()
    {
        $categorias = Categoria::all();

        return response()->json([
            'message' => 'Listado de categorías obtenido correctamente.',
            'data'    => $categorias,
        ], 200);
    }

    // GET /api/categorias/{categoria}
    public function show(Categoria $categoria)
    {
        return response()->json([
            'message' => 'Categoría obtenida correctamente.',
            'data'    => $categoria,
        ], 200);
    }

    // POST /api/categorias
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'tipo'   => ['nullable', 'string', 'max:50'],
        ], [
            'nombre.required' => 'El nombre de la categoría es obligatorio.',
            'nombre.max'      => 'El nombre no puede exceder los 100 caracteres.',
            'tipo.max'        => 'El tipo no puede exceder los 50 caracteres.',
        ]);

        $categoria = Categoria::create($validatedData);

        return response()->json([
            'message' => 'Categoría creada correctamente.',
            'data'    => $categoria,
        ], 201);
    }

    // PUT/PATCH /api/categorias/{categoria}
    public function update(Request $request, Categoria $categoria)
    {
        $validatedData = $request->validate([
            'nombre' => ['sometimes', 'required', 'string', 'max:100'],
            'tipo'   => ['nullable', 'string', 'max:50'],
        ], [
            'nombre.required' => 'El nombre de la categoría es obligatorio.',
            'nombre.max'      => 'El nombre no puede exceder los 100 caracteres.',
            'tipo.max'        => 'El tipo no puede exceder los 50 caracteres.',
        ]);

        $categoria->update($validatedData);

        return response()->json([
            'message' => 'Categoría actualizada correctamente.',
            'data'    => $categoria,
        ], 200);
    }

    // DELETE /api/categorias/{categoria}
    public function destroy(Categoria $categoria)
    {
        $categoria->delete();

        return response()->json([
            'message' => 'Categoría eliminada correctamente.',
        ], 200);
    }
}
