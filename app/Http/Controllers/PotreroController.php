<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Potrero;
use Illuminate\Http\Request;

class PotreroController extends Controller
{
    // GET /api/potreros
    public function index()
    {
        $potreros = Potrero::all();

        return response()->json([
            'message' => 'Listado de potreros obtenido correctamente.',
            'data'    => $potreros,
        ], 200);
    }

    // GET /api/potreros/{potrero}
    public function show(Potrero $potrero)
    {
        return response()->json([
            'message' => 'Potrero obtenido correctamente.',
            'data'    => $potrero,
        ], 200);
    }

    // POST /api/potreros
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nombre'                 => ['required', 'string', 'max:100'],
            'hectareas_de_extension' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'capacidad_maxima'       => ['required', 'integer', 'min:0'],
            'estado_pasto'           => ['nullable', 'string', 'max:50'],
        ], [
            'nombre.required'                 => 'El nombre del potrero es obligatorio.',
            'nombre.max'                       => 'El nombre no puede exceder los 100 caracteres.',
            'hectareas_de_extension.required'  => 'Las hectáreas de extensión son obligatorias.',
            'hectareas_de_extension.numeric'   => 'Las hectáreas de extensión deben ser un valor numérico.',
            'hectareas_de_extension.min'       => 'Las hectáreas de extensión no pueden ser negativas.',
            'capacidad_maxima.required'        => 'La capacidad máxima es obligatoria.',
            'capacidad_maxima.integer'         => 'La capacidad máxima debe ser un número entero.',
            'capacidad_maxima.min'             => 'La capacidad máxima no puede ser negativa.',
            'estado_pasto.max'                 => 'El estado del pasto no puede exceder los 50 caracteres.',
        ]);

        $potrero = Potrero::create($validatedData);

        return response()->json([
            'message' => 'Potrero creado correctamente.',
            'data'    => $potrero,
        ], 201);
    }

    // PUT/PATCH /api/potreros/{potrero}
    public function update(Request $request, Potrero $potrero)
    {
        $validatedData = $request->validate([
            'nombre'                 => ['sometimes', 'required', 'string', 'max:100'],
            'hectareas_de_extension' => ['sometimes', 'required', 'numeric', 'min:0', 'max:999999.99'],
            'capacidad_maxima'       => ['sometimes', 'required', 'integer', 'min:0'],
            'estado_pasto'           => ['nullable', 'string', 'max:50'],
        ], [
            'nombre.required'                 => 'El nombre del potrero es obligatorio.',
            'nombre.max'                       => 'El nombre no puede exceder los 100 caracteres.',
            'hectareas_de_extension.required'  => 'Las hectáreas de extensión son obligatorias.',
            'hectareas_de_extension.numeric'   => 'Las hectáreas de extensión deben ser un valor numérico.',
            'hectareas_de_extension.min'       => 'Las hectáreas de extensión no pueden ser negativas.',
            'capacidad_maxima.required'        => 'La capacidad máxima es obligatoria.',
            'capacidad_maxima.integer'         => 'La capacidad máxima debe ser un número entero.',
            'capacidad_maxima.min'             => 'La capacidad máxima no puede ser negativa.',
            'estado_pasto.max'                 => 'El estado del pasto no puede exceder los 50 caracteres.',
        ]);

        $potrero->update($validatedData);

        return response()->json([
            'message' => 'Potrero actualizado correctamente.',
            'data'    => $potrero,
        ], 200);
    }

    // DELETE /api/potreros/{potrero}
    public function destroy(Potrero $potrero)
    {
        $potrero->delete();

        return response()->json([
            'message' => 'Potrero eliminado correctamente.',
        ], 200);
    }
}
