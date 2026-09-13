<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UnidadMedida;
use Illuminate\Http\Request;

class UnidadMedidaController extends Controller
{
    // GET /api/unidades-medida
    public function index()
    {
        $unidades = UnidadMedida::all();

        return response()->json([
            'message' => 'Listado de unidades de medida obtenido correctamente.',
            'data'    => $unidades,
        ], 200);
    }

    // GET /api/unidades-medida/{unidad_medida}
    public function show(UnidadMedida $unidad_medida)
    {
        return response()->json([
            'message' => 'Unidad de medida obtenida correctamente.',
            'data'    => $unidad_medida,
        ], 200);
    }

    // POST /api/unidades-medida
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nombre'   => ['required', 'string', 'max:100'],
            'apertura' => ['nullable', 'string', 'max:20'],
        ], [
            'nombre.required' => 'El nombre de la unidad de medida es obligatorio.',
            'nombre.max'      => 'El nombre no puede exceder los 100 caracteres.',
            'apertura.max'    => 'La apertura (abreviatura) no puede exceder los 20 caracteres.',
        ]);

        $unidad = UnidadMedida::create($validatedData);

        return response()->json([
            'message' => 'Unidad de medida creada correctamente.',
            'data'    => $unidad,
        ], 201);
    }

    // PUT/PATCH /api/unidades-medida/{unidad_medida}
    public function update(Request $request, UnidadMedida $unidad_medida)
    {
        $validatedData = $request->validate([
            'nombre'   => ['sometimes', 'required', 'string', 'max:100'],
            'apertura' => ['nullable', 'string', 'max:20'],
        ], [
            'nombre.required' => 'El nombre de la unidad de medida es obligatorio.',
            'nombre.max'      => 'El nombre no puede exceder los 100 caracteres.',
            'apertura.max'    => 'La apertura (abreviatura) no puede exceder los 20 caracteres.',
        ]);

        $unidad_medida->update($validatedData);

        return response()->json([
            'message' => 'Unidad de medida actualizada correctamente.',
            'data'    => $unidad_medida,
        ], 200);
    }

    // DELETE /api/unidades-medida/{unidad_medida}
    public function destroy(UnidadMedida $unidad_medida)
    {
        $unidad_medida->delete();

        return response()->json([
            'message' => 'Unidad de medida eliminada correctamente.',
        ], 200);
    }
}
