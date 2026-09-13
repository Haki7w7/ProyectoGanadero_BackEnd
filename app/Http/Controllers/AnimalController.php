<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Animal;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AnimalController extends Controller
{
    // GET /api/animales
    public function index()
    {
        $animales = Animal::with(['raza', 'potrero'])->get();

        return response()->json([
            'message' => 'Listado de animales obtenido correctamente.',
            'data'    => $animales,
        ], 200);
    }

    // GET /api/animales/{animal}
    public function show(Animal $animal)
    {
        $animal->load(['raza', 'potrero', 'pesajes', 'tratamientos']);

        return response()->json([
            'message' => 'Animal obtenido correctamente.',
            'data'    => $animal,
        ], 200);
    }

    // POST /api/animales
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'numero_arete'     => ['required', 'string', 'max:50', 'unique:animales,numero_arete'],
            'raza_id'          => ['required', 'integer', 'exists:razas,raza_id'],
            'sexo'             => ['required', 'string', Rule::in(['Macho', 'Hembra'])],
            'fecha_nacimiento' => ['nullable', 'date', 'before_or_equal:today'],
            'estado'           => ['nullable', 'string', 'max:50'],
            'potrero_id'       => ['required', 'integer', 'exists:potreros,potrero_id'],
        ], [
            'numero_arete.required'            => 'El número de arete es obligatorio.',
            'numero_arete.unique'              => 'Ya existe un animal registrado con ese número de arete.',
            'numero_arete.max'                 => 'El número de arete no puede exceder los 50 caracteres.',
            'raza_id.required'                 => 'La raza es obligatoria.',
            'raza_id.exists'                   => 'La raza seleccionada no existe.',
            'sexo.required'                    => 'El sexo es obligatorio.',
            'sexo.in'                          => 'El sexo debe ser Macho o Hembra.',
            'fecha_nacimiento.date'            => 'La fecha de nacimiento no es una fecha válida.',
            'fecha_nacimiento.before_or_equal' => 'La fecha de nacimiento no puede ser futura.',
            'estado.max'                       => 'El estado no puede exceder los 50 caracteres.',
            'potrero_id.required'              => 'El potrero es obligatorio.',
            'potrero_id.exists'                => 'El potrero seleccionado no existe.',
        ]);

        $animal = Animal::create($validatedData);

        return response()->json([
            'message' => 'Animal creado correctamente.',
            'data'    => $animal,
        ], 201);
    }

    // PUT/PATCH /api/animales/{animal}
    public function update(Request $request, Animal $animal)
    {
        $validatedData = $request->validate([
            'numero_arete' => [
                'sometimes', 'required', 'string', 'max:50',
                Rule::unique('animales', 'numero_arete')->ignore($animal->id_animal, 'id_animal'),
            ],
            'raza_id'          => ['sometimes', 'required', 'integer', 'exists:razas,raza_id'],
            'sexo'             => ['sometimes', 'required', 'string', Rule::in(['Macho', 'Hembra'])],
            'fecha_nacimiento' => ['nullable', 'date', 'before_or_equal:today'],
            'estado'           => ['nullable', 'string', 'max:50'],
            'potrero_id'       => ['sometimes', 'required', 'integer', 'exists:potreros,potrero_id'],
        ], [
            'numero_arete.required'            => 'El número de arete es obligatorio.',
            'numero_arete.unique'              => 'Ya existe un animal registrado con ese número de arete.',
            'numero_arete.max'                 => 'El número de arete no puede exceder los 50 caracteres.',
            'raza_id.required'                 => 'La raza es obligatoria.',
            'raza_id.exists'                   => 'La raza seleccionada no existe.',
            'sexo.required'                    => 'El sexo es obligatorio.',
            'sexo.in'                          => 'El sexo debe ser Macho o Hembra.',
            'fecha_nacimiento.date'            => 'La fecha de nacimiento no es una fecha válida.',
            'fecha_nacimiento.before_or_equal' => 'La fecha de nacimiento no puede ser futura.',
            'estado.max'                       => 'El estado no puede exceder los 50 caracteres.',
            'potrero_id.required'              => 'El potrero es obligatorio.',
            'potrero_id.exists'                => 'El potrero seleccionado no existe.',
        ]);

        $animal->update($validatedData);

        return response()->json([
            'message' => 'Animal actualizado correctamente.',
            'data'    => $animal,
        ], 200);
    }

    // DELETE /api/animales/{animal}
    public function destroy(Animal $animal)
    {
        $animal->delete();

        return response()->json([
            'message' => 'Animal eliminado correctamente.',
        ], 200);
    }
}
