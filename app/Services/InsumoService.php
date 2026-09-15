<?php

namespace App\Services;

use App\Exceptions\ReglaNegocioException;
use App\Models\Insumo;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class InsumoService
{
    /**
     * Lista insumos aplicando filtros combinables, ordenamiento dinámico
     * y paginación con tope de seguridad.
     */
    public function listarInsumos(array $filtros = []): LengthAwarePaginator
    {
        // Paginación segura con tope en 100
        $perPage = min(max((int) ($filtros['per_page'] ?? 15), 1), 100);

        // Ordenamiento por al menos dos campos válidos
        $allowedSortFields = ['nombre', 'precio', 'insumo_id', 'categoria_id'];
        $sortBy = in_array($filtros['sort_by'] ?? '', $allowedSortFields, true) ? $filtros['sort_by'] : 'insumo_id';
        $sortOrder = strtolower($filtros['order'] ?? 'asc') === 'desc' ? 'desc' : 'asc';

        return Insumo::query()
            ->when(!empty($filtros['categoria_id']), fn ($query) => $query->where('categoria_id', (int) $filtros['categoria_id']))
            ->when(!empty($filtros['unidad_medida_id']), fn ($query) => $query->where('unidad_medida_id', (int) $filtros['unidad_medida_id']))
            ->when(!empty($filtros['buscar'] ?? $filtros['nombre'] ?? null), function ($query) use ($filtros) {
                $termino = $filtros['buscar'] ?? $filtros['nombre'];
                $query->where('nombre', 'like', '%' . $termino . '%');
            })
            ->when(isset($filtros['precio_min']), fn ($query) => $query->where('precio', '>=', (float) $filtros['precio_min']))
            ->when(isset($filtros['precio_max']), fn ($query) => $query->where('precio', '<=', (float) $filtros['precio_max']))
            ->with(['categoria', 'unidadMedida'])
            ->orderBy($sortBy, $sortOrder)
            ->paginate($perPage);
    }

    public function obtenerPorId(int $id): Insumo
    {
        $insumo = Insumo::with(['categoria', 'unidadMedida'])->find($id);

        if (!$insumo) {
            throw new ReglaNegocioException('El insumo solicitado no existe.', 404);
        }

        return $insumo;
    }

    public function crearInsumo(array $datos): Insumo
    {
        return DB::transaction(function () use ($datos) {
            return Insumo::create($datos);
        });
    }

    public function actualizarInsumo(int $id, array $datos): Insumo
    {
        $insumo = $this->obtenerPorId($id);

        DB::transaction(function () use ($insumo, $datos) {
            $insumo->update($datos);
        });

        return $insumo->refresh();
    }

    public function eliminarInsumo(int $id): bool
    {
        $insumo = $this->obtenerPorId($id);

        return (bool) $insumo->delete();
    }
}
