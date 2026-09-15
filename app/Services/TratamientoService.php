<?php

namespace App\Services;

use App\Exceptions\ReglaNegocioException;
use App\Models\Tratamiento;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class TratamientoService
{
    /**
     * Lista tratamientos aplicando filtros combinables, ordenamiento dinámico
     * y paginación con tope de seguridad.
     */
    public function listarTratamientos(array $filtros = []): LengthAwarePaginator
    {
        // Paginación segura con tope en 100
        $perPage = min(max((int) ($filtros['per_page'] ?? 15), 1), 100);

        // Ordenamiento por al menos dos campos válidos
        $allowedSortFields = ['nombre', 'tipo', 'tratamiento_id', 'created_at'];
        $sortBy = in_array($filtros['sort_by'] ?? '', $allowedSortFields, true) ? $filtros['sort_by'] : 'tratamiento_id';
        $sortOrder = strtolower($filtros['order'] ?? 'asc') === 'desc' ? 'desc' : 'asc';

        return Tratamiento::query()
            ->when(!empty($filtros['tipo']), fn ($query) => $query->where('tipo', $filtros['tipo']))
            ->when(!empty($filtros['buscar'] ?? $filtros['nombre'] ?? null), function ($query) use ($filtros) {
                $termino = $filtros['buscar'] ?? $filtros['nombre'];
                $query->where('nombre', 'like', '%' . $termino . '%');
            })
            ->orderBy($sortBy, $sortOrder)
            ->paginate($perPage);
    }

    public function obtenerPorId(int $id): Tratamiento
    {
        $tratamiento = Tratamiento::find($id);

        if (!$tratamiento) {
            throw new ReglaNegocioException('El tratamiento solicitado no existe.', 404);
        }

        return $tratamiento;
    }

    public function crearTratamiento(array $datos): Tratamiento
    {
        return DB::transaction(function () use ($datos) {
            return Tratamiento::create($datos);
        });
    }

    public function actualizarTratamiento(int $id, array $datos): Tratamiento
    {
        $tratamiento = $this->obtenerPorId($id);

        DB::transaction(function () use ($tratamiento, $datos) {
            $tratamiento->update($datos);
        });

        return $tratamiento->refresh();
    }

    /**
     * Elimina un tratamiento, aplicando una regla de negocio: no se puede
     * eliminar si ya tiene aplicaciones registradas a algún animal.
     */
    public function eliminarTratamiento(int $id): bool
    {
        $tratamiento = $this->obtenerPorId($id);

        if ($tratamiento->aplicaciones()->exists()) {
            throw new ReglaNegocioException(
                'No se puede eliminar el tratamiento porque tiene aplicaciones registradas.',
                422
            );
        }

        return (bool) $tratamiento->delete();
    }
}
