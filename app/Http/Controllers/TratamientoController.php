<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tratamiento;
use Illuminate\Http\Request;

class TratamientoController extends Controller
{
    // GET /api/tratamientos
    public function index()
    {
        $tratamientos = Tratamiento::all();

        return response()->json([
            'message' => 'Listado de tratamientos obtenido correctamente.',
            'data'    => $tratamientos,
        ], 200);
    }

    // GET /api/tratamientos/{tratamiento}
    public function show(Tratamiento $tratamiento)
    {
        return response()->json([
            'message' => 'Tratamiento obtenido correctamente.',
            'data'    => $tratamiento,
        ], 200);
    }

    // POST /api/tratamientos
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nombre'      => ['required', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string'],
            'tipo'        => ['nullable', 'string', 'max:50'],
        ], [
            'nombre.required' => 'El nombre del tratamiento es obligatorio.',
            'nombre.max'      => 'El nombre no puede exceder los 100 caracteres.',
            'tipo.max'        => 'El tipo no puede exceder los 50 caracteres.',
        ]);

        $tratamiento = Tratamiento::create($validatedData);

        return response()->json([
            'message' => 'Tratamiento creado correctamente.',
            'data'    => $tratamiento,
        ], 201);
    }

    // PUT/PATCH /api/tratamientos/{tratamiento}
    public function update(Request $request, Tratamiento $tratamiento)
    {
        $validatedData = $request->validate([
            'nombre'      => ['sometimes', 'required', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string'],
            'tipo'        => ['nullable', 'string', 'max:50'],
        ], [
            'nombre.required' => 'El nombre del tratamiento es obligatorio.',
            'nombre.max'      => 'El nombre no puede exceder los 100 caracteres.',
            'tipo.max'        => 'El tipo no puede exceder los 50 caracteres.',
        ]);

        $tratamiento->update($validatedData);

        return response()->json([
            'message' => 'Tratamiento actualizado correctamente.',
            'data'    => $tratamiento,
        ], 200);
    }

    // DELETE /api/tratamientos/{tratamiento}
    public function destroy(Tratamiento $tratamiento)
    {
        $tratamiento->delete();

        return response()->json([
            'message' => 'Tratamiento eliminado correctamente.',
        ], 200);
    }
}
