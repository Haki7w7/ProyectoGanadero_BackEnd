<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pesaje;
use Illuminate\Http\Request;

class PesajeController extends Controller
{
    // GET /api/pesajes
    public function index()
    {
        $pesajes = Pesaje::with('animal')->get();

        return response()->json([
            'message' => 'Listado de pesajes obtenido correctamente.',
            'data'    => $pesajes,
        ], 200);
    }

    // GET /api/pesajes/{pesaje}
    public function show(Pesaje $pesaje)
    {
        $pesaje->load('animal');

        return response()->json([
            'message' => 'Pesaje obtenido correctamente.',
            'data'    => $pesaje,
        ], 200);
    }

    // POST /api/pesajes
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'id_animal'     => ['required', 'integer', 'exists:animales,id_animal'],
            'peso_kg'       => ['required', 'numeric', 'min:0', 'max:9999.99'],
            'fecha_pesaje'  => ['required', 'date'],
            'observaciones' => ['nullable', 'string'],
        ], [
            'id_animal.required'    => 'El animal es obligatorio.',
            'id_animal.exists'      => 'El animal seleccionado no existe.',
            'peso_kg.required'      => 'El peso es obligatorio.',
            'peso_kg.numeric'       => 'El peso debe ser un valor numérico.',
            'peso_kg.min'           => 'El peso no puede ser negativo.',
            'peso_kg.max'           => 'El peso excede el valor máximo permitido.',
            'fecha_pesaje.required' => 'La fecha del pesaje es obligatoria.',
            'fecha_pesaje.date'     => 'La fecha del pesaje no es una fecha válida.',
        ]);

        $pesaje = Pesaje::create($validatedData);

        return response()->json([
            'message' => 'Pesaje registrado correctamente.',
            'data'    => $pesaje,
        ], 201);
    }

    // PUT/PATCH /api/pesajes/{pesaje}
    public function update(Request $request, Pesaje $pesaje)
    {
        $validatedData = $request->validate([
            'id_animal'     => ['sometimes', 'required', 'integer', 'exists:animales,id_animal'],
            'peso_kg'       => ['sometimes', 'required', 'numeric', 'min:0', 'max:9999.99'],
            'fecha_pesaje'  => ['sometimes', 'required', 'date'],
            'observaciones' => ['nullable', 'string'],
        ], [
            'id_animal.required'    => 'El animal es obligatorio.',
            'id_animal.exists'      => 'El animal seleccionado no existe.',
            'peso_kg.required'      => 'El peso es obligatorio.',
            'peso_kg.numeric'       => 'El peso debe ser un valor numérico.',
            'peso_kg.min'           => 'El peso no puede ser negativo.',
            'peso_kg.max'           => 'El peso excede el valor máximo permitido.',
            'fecha_pesaje.required' => 'La fecha del pesaje es obligatoria.',
            'fecha_pesaje.date'     => 'La fecha del pesaje no es una fecha válida.',
        ]);

        $pesaje->update($validatedData);

        return response()->json([
            'message' => 'Pesaje actualizado correctamente.',
            'data'    => $pesaje,
        ], 200);
    }

    // DELETE /api/pesajes/{pesaje}
    public function destroy(Pesaje $pesaje)
    {
        $pesaje->delete();

        return response()->json([
            'message' => 'Pesaje eliminado correctamente.',
        ], 200);
    }
}