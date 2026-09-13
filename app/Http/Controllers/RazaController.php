<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Raza;
use Illuminate\Http\Request;

class RazaController extends Controller
{
    // GET /api/razas
    public function index()
    {
        $razas = Raza::all();

        return response()->json([
            'message' => 'Listado de razas obtenido correctamente.',
            'data'    => $razas,
        ], 200);
    }

    // GET /api/razas/{raza}
    public function show(Raza $raza)
    {
        return response()->json([
            'message' => 'Raza obtenida correctamente.',
            'data'    => $raza,
        ], 200);
    }

    // POST /api/razas
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
        ], [
            'nombre.required' => 'El nombre de la raza es obligatorio.',
            'nombre.max'      => 'El nombre no puede exceder los 100 caracteres.',
        ]);

        $raza = Raza::create($validatedData);

        return response()->json([
            'message' => 'Raza creada correctamente.',
            'data'    => $raza,
        ], 201);
    }

    // PUT/PATCH /api/razas/{raza}
    public function update(Request $request, Raza $raza)
    {
        $validatedData = $request->validate([
            'nombre' => ['sometimes', 'required', 'string', 'max:100'],
        ], [
            'nombre.required' => 'El nombre de la raza es obligatorio.',
            'nombre.max'      => 'El nombre no puede exceder los 100 caracteres.',
        ]);

        $raza->update($validatedData);

        return response()->json([
            'message' => 'Raza actualizada correctamente.',
            'data'    => $raza,
        ], 200);
    }

    // DELETE /api/razas/{raza}
    public function destroy(Raza $raza)
    {
        $raza->delete();

        return response()->json([
            'message' => 'Raza eliminada correctamente.',
        ], 200);
    }
}
