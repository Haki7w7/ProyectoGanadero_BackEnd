<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Insumo;
use Illuminate\Http\Request;

class InsumoController extends Controller
{
    // GET /api/insumos
    public function index()
    {
        $insumos = Insumo::with(['categoria', 'unidadMedida'])->get();

        return response()->json([
            'message' => 'Listado de insumos obtenido correctamente.',
            'data'    => $insumos,
        ], 200);
    }

    // GET /api/insumos/{insumo}
    public function show(Insumo $insumo)
    {
        $insumo->load(['categoria', 'unidadMedida']);

        return response()->json([
            'message' => 'Insumo obtenido correctamente.',
            'data'    => $insumo,
        ], 200);
    }

    // POST /api/insumos
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nombre'           => ['required', 'string', 'max:150'],
            'categoria_id'     => ['required', 'integer', 'exists:categorias,categoria_id'],
            'precio'           => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'unidad_medida_id' => ['required', 'integer', 'exists:unidades_medida,unidad_medida_id'],
        ], [
            'nombre.required'           => 'El nombre del insumo es obligatorio.',
            'nombre.max'                => 'El nombre no puede exceder los 150 caracteres.',
            'categoria_id.required'     => 'La categoría es obligatoria.',
            'categoria_id.exists'       => 'La categoría seleccionada no existe.',
            'precio.required'           => 'El precio es obligatorio.',
            'precio.numeric'            => 'El precio debe ser un valor numérico.',
            'precio.min'                => 'El precio no puede ser negativo.',
            'unidad_medida_id.required' => 'La unidad de medida es obligatoria.',
            'unidad_medida_id.exists'   => 'La unidad de medida seleccionada no existe.',
        ]);

        $insumo = Insumo::create($validatedData);

        return response()->json([
            'message' => 'Insumo creado correctamente.',
            'data'    => $insumo,
        ], 201);
    }

    // PUT/PATCH /api/insumos/{insumo}
    public function update(Request $request, Insumo $insumo)
    {
        $validatedData = $request->validate([
            'nombre'           => ['sometimes', 'required', 'string', 'max:150'],
            'categoria_id'     => ['sometimes', 'required', 'integer', 'exists:categorias,categoria_id'],
            'precio'           => ['sometimes', 'required', 'numeric', 'min:0', 'max:99999999.99'],
            'unidad_medida_id' => ['sometimes', 'required', 'integer', 'exists:unidades_medida,unidad_medida_id'],
        ], [
            'nombre.required'           => 'El nombre del insumo es obligatorio.',
            'nombre.max'                => 'El nombre no puede exceder los 150 caracteres.',
            'categoria_id.required'     => 'La categoría es obligatoria.',
            'categoria_id.exists'       => 'La categoría seleccionada no existe.',
            'precio.required'           => 'El precio es obligatorio.',
            'precio.numeric'            => 'El precio debe ser un valor numérico.',
            'precio.min'                => 'El precio no puede ser negativo.',
            'unidad_medida_id.required' => 'La unidad de medida es obligatoria.',
            'unidad_medida_id.exists'   => 'La unidad de medida seleccionada no existe.',
        ]);

        $insumo->update($validatedData);

        return response()->json([
            'message' => 'Insumo actualizado correctamente.',
            'data'    => $insumo,
        ], 200);
    }

    // DELETE /api/insumos/{insumo}
    public function destroy(Insumo $insumo)
    {
        $insumo->delete();

        return response()->json([
            'message' => 'Insumo eliminado correctamente.',
        ], 200);
    }
}
