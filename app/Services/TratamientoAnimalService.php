<?php

namespace App\Services;

use App\Exceptions\ReglaNegocioException;
use App\Models\TratamientoAnimal;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class TratamientoAnimalService
{
    /**
     * Lista aplicaciones de tratamiento aplicando filtros opcionales,
     * delegando en los scopes definidos en el modelo TratamientoAnimal.
     *
     * Filtros soportados: id_animal, tratamiento_id.
     */
    public function listarTratamientoAnimal(array $filtros = []): Collection
    {
        return TratamientoAnimal::query()
            ->when(!empty($filtros['id_animal']), fn ($query) => $query->porAnimal((int) $filtros['id_animal']))
            ->when(!empty($filtros['tratamiento_id']), fn ($query) => $query->porTratamiento((int) $filtros['tratamiento_id']))
            ->with(['animal', 'tratamiento'])
            ->get();
    }

    public function obtenerPorId(int $id): TratamientoAnimal
    {
        $registro = TratamientoAnimal::with(['animal', 'tratamiento'])->find($id);

        if (!$registro) {
            throw new ReglaNegocioException('El registro de aplicación de tratamiento no existe.', 404);
        }

        return $registro;
    }

    public function crearTratamientoAnimal(array $datos): TratamientoAnimal
    {
        return DB::transaction(function () use ($datos) {
            return TratamientoAnimal::create($datos);
        });
    }

    public function actualizarTratamientoAnimal(int $id, array $datos): TratamientoAnimal
    {
        $registro = $this->obtenerPorId($id);

        DB::transaction(function () use ($registro, $datos) {
            $registro->update($datos);
        });

        return $registro->refresh();
    }

    public function eliminarTratamientoAnimal(int $id): bool
    {
        $registro = $this->obtenerPorId($id);

        return (bool) $registro->delete();
    }
}
