<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\TratamientoAnimal;
use Illuminate\Http\Request;

class TratamientoAnimalController extends Controller
{
    // GET /api/tratamiento-animal
    public function index()
    {
        $registros = TratamientoAnimal::with(['animal', 'tratamiento'])->get();

        return response()->json([
            'message' => 'Listado de aplicaciones de tratamiento obtenido correctamente.',
            'data'    => $registros,
        ], 200);
    }

    // GET /api/tratamiento-animal/{tratamiento_animal}
    public function show(TratamientoAnimal $tratamiento_animal)
    {
        $tratamiento_animal->load(['animal', 'tratamiento']);

        return response()->json([
            'message' => 'Aplicación de tratamiento obtenida correctamente.',
            'data'    => $tratamiento_animal,
        ], 200);
    }

    // POST /api/tratamiento-animal
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'id_animal'        => ['required', 'integer', 'exists:animales,id_animal'],
            'tratamiento_id'   => ['required', 'integer', 'exists:tratamientos,tratamiento_id'],
            'fecha_aplicacion' => ['required', 'date'],
            'dosis_ml'         => ['required', 'numeric', 'min:0', 'max:9999.99'],
            'observaciones'    => ['nullable', 'string'],
        ], [
            'id_animal.required'        => 'El animal es obligatorio.',
            'id_animal.exists'          => 'El animal seleccionado no existe.',
            'tratamiento_id.required'   => 'El tratamiento es obligatorio.',
            'tratamiento_id.exists'     => 'El tratamiento seleccionado no existe.',
            'fecha_aplicacion.required' => 'La fecha de aplicación es obligatoria.',
            'fecha_aplicacion.date'     => 'La fecha de aplicación no es una fecha válida.',
            'dosis_ml.required'         => 'La dosis es obligatoria.',
            'dosis_ml.numeric'          => 'La dosis debe ser un valor numérico.',
            'dosis_ml.min'              => 'La dosis no puede ser negativa.',
        ]);

        $registro = TratamientoAnimal::create($validatedData);

        return response()->json([
            'message' => 'Aplicación de tratamiento registrada correctamente.',
            'data'    => $registro,
        ], 201);
    }

    // PUT/PATCH /api/tratamiento-animal/{tratamiento_animal}
    public function update(Request $request, TratamientoAnimal $tratamiento_animal)
    {
        $validatedData = $request->validate([
            'id_animal'        => ['sometimes', 'required', 'integer', 'exists:animales,id_animal'],
            'tratamiento_id'   => ['sometimes', 'required', 'integer', 'exists:tratamientos,tratamiento_id'],
            'fecha_aplicacion' => ['sometimes', 'required', 'date'],
            'dosis_ml'         => ['sometimes', 'required', 'numeric', 'min:0', 'max:9999.99'],
            'observaciones'    => ['nullable', 'string'],
        ], [
            'id_animal.required'        => 'El animal es obligatorio.',
            'id_animal.exists'          => 'El animal seleccionado no existe.',
            'tratamiento_id.required'   => 'El tratamiento es obligatorio.',
            'tratamiento_id.exists'     => 'El tratamiento seleccionado no existe.',
            'fecha_aplicacion.required' => 'La fecha de aplicación es obligatoria.',
            'fecha_aplicacion.date'     => 'La fecha de aplicación no es una fecha válida.',
            'dosis_ml.required'         => 'La dosis es obligatoria.',
            'dosis_ml.numeric'          => 'La dosis debe ser un valor numérico.',
            'dosis_ml.min'              => 'La dosis no puede ser negativa.',
        ]);

        $tratamiento_animal->update($validatedData);

        return response()->json([
            'message' => 'Aplicación de tratamiento actualizada correctamente.',
            'data'    => $tratamiento_animal,
        ], 200);
    }

    // DELETE /api/tratamiento-animal/{tratamiento_animal}
    public function destroy(TratamientoAnimal $tratamiento_animal)
    {
        $tratamiento_animal->delete();

        return response()->json([
            'message' => 'Aplicación de tratamiento eliminada correctamente.',
        ], 200);
    }
}
