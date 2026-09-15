<?php

namespace App\Services;

use App\Exceptions\ReglaNegocioException;
use App\Models\UnidadMedida;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class UnidadMedidaService
{
    /**
     * Lista unidades de medida aplicando filtros combinables, ordenamiento dinámico
     * y paginación con tope de seguridad.
     */
    public function listarUnidadesMedida(array $filtros = []): LengthAwarePaginator
    {
        // Paginación segura con tope en 100
        $perPage = min(max((int) ($filtros['per_page'] ?? 15), 1), 100);

        // Ordenamiento por al menos dos campos válidos
        $allowedSortFields = ['nombre', 'abreviatura', 'unidad_medida_id', 'created_at'];
        $sortBy = in_array($filtros['sort_by'] ?? '', $allowedSortFields, true) ? $filtros['sort_by'] : 'unidad_medida_id';
        $sortOrder = strtolower($filtros['order'] ?? 'asc') === 'desc' ? 'desc' : 'asc';

        return UnidadMedida::query()
            ->when(!empty($filtros['buscar'] ?? $filtros['nombre'] ?? null), function ($query) use ($filtros) {
                $termino = $filtros['buscar'] ?? $filtros['nombre'];
                $query->where('nombre', 'like', '%' . $termino . '%');
            })
            ->when(!empty($filtros['abreviatura']), fn ($query) => $query->where('abreviatura', $filtros['abreviatura']))
            ->orderBy($sortBy, $sortOrder)
            ->paginate($perPage);
    }

    public function obtenerPorId(int $id): UnidadMedida
    {
        $unidad = UnidadMedida::find($id);

        if (!$unidad) {
            throw new ReglaNegocioException('La unidad de medida solicitada no existe.', 404);
        }

        return $unidad;
    }

    public function crearUnidadMedida(array $datos): UnidadMedida
    {
        return DB::transaction(function () use ($datos) {
            return UnidadMedida::create($datos);
        });
    }

    public function actualizarUnidadMedida(int $id, array $datos): UnidadMedida
    {
        $unidad = $this->obtenerPorId($id);

        DB::transaction(function () use ($unidad, $datos) {
            $unidad->update($datos);
        });

        return $unidad->refresh();
    }

    /**
     * Elimina una unidad de medida, aplicando una regla de negocio: no se
     * puede eliminar si tiene insumos asociados.
     */
    public function eliminarUnidadMedida(int $id): bool
    {
        $unidad = $this->obtenerPorId($id);

        if ($unidad->insumos()->exists()) {
            throw new ReglaNegocioException(
                'No se puede eliminar la unidad de medida porque tiene insumos asociados.',
                422
            );
        }

        return (bool) $unidad->delete();
    }
}
