<?php

namespace App\Services;

use App\Exceptions\ReglaNegocioException;
use App\Models\TratamientoAnimal;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class TratamientoAnimalService
{
    /**
     * Lista aplicaciones de tratamiento aplicando filtros combinables, ordenamiento dinámico
     * y paginación con tope de seguridad.
     */
    public function listarTratamientoAnimal(array $filtros = []): LengthAwarePaginator
    {
        // Paginación segura con tope en 100
        $perPage = min(max((int) ($filtros['per_page'] ?? 15), 1), 100);

        // Ordenamiento por al menos dos campos válidos
        $allowedSortFields = ['fecha_aplicacion', 'dosis_ml', 'tratamiento_animal_id', 'id_animal', 'tratamiento_id'];
        $sortBy = in_array($filtros['sort_by'] ?? '', $allowedSortFields, true) ? $filtros['sort_by'] : 'fecha_aplicacion';
        $sortOrder = strtolower($filtros['order'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        return TratamientoAnimal::query()
            ->when(!empty($filtros['id_animal']), fn ($query) => $query->where('id_animal', (int) $filtros['id_animal']))
            ->when(!empty($filtros['tratamiento_id']), fn ($query) => $query->where('tratamiento_id', (int) $filtros['tratamiento_id']))
            ->when(!empty($filtros['fecha_desde']), fn ($query) => $query->whereDate('fecha_aplicacion', '>=', $filtros['fecha_desde']))
            ->when(!empty($filtros['fecha_hasta']), fn ($query) => $query->whereDate('fecha_aplicacion', '<=', $filtros['fecha_hasta']))
            ->with(['animal', 'tratamiento'])
            ->orderBy($sortBy, $sortOrder)
            ->paginate($perPage);
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
        Gate::authorize('create', TratamientoAnimal::class); // CAPA 2

        return DB::transaction(function () use ($datos) {
            return TratamientoAnimal::create($datos);
        });
    }

    public function actualizarTratamientoAnimal(int $id, array $datos): TratamientoAnimal
    {
        $registro = $this->obtenerPorId($id);

        Gate::authorize('update', $registro); // CAPA 2

        DB::transaction(function () use ($registro, $datos) {
            $registro->update($datos);
        });

        return $registro->refresh();
    }

    public function eliminarTratamientoAnimal(int $id): bool
    {
        $registro = $this->obtenerPorId($id);

        Gate::authorize('delete', $registro); // CAPA 2

        return (bool) $registro->delete();
    }
}
