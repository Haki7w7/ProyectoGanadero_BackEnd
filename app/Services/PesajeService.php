<?php

namespace App\Services;

use App\Exceptions\ReglaNegocioException;
use App\Models\Pesaje;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class PesajeService
{
    /**
     * Lista pesajes aplicando filtros combinables, ordenamiento dinámico
     * y paginación con tope de seguridad.
     */
    public function listarPesajes(array $filtros = []): LengthAwarePaginator
    {
        // Paginación segura con tope en 100
        $perPage = min(max((int) ($filtros['per_page'] ?? 15), 1), 100);

        // Ordenamiento por al menos dos campos válidos
        $allowedSortFields = ['fecha_pesaje', 'peso_kg', 'pesaje_id', 'id_animal'];
        $sortBy = in_array($filtros['sort_by'] ?? '', $allowedSortFields, true) ? $filtros['sort_by'] : 'fecha_pesaje';
        $sortOrder = strtolower($filtros['order'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        return Pesaje::query()
            ->when(!empty($filtros['id_animal']), fn ($query) => $query->where('id_animal', (int) $filtros['id_animal']))
            ->when(!empty($filtros['fecha_desde']), fn ($query) => $query->whereDate('fecha_pesaje', '>=', $filtros['fecha_desde']))
            ->when(!empty($filtros['fecha_hasta']), fn ($query) => $query->whereDate('fecha_pesaje', '<=', $filtros['fecha_hasta']))
            ->when(isset($filtros['peso_min']), fn ($query) => $query->where('peso_kg', '>=', (float) $filtros['peso_min']))
            ->when(isset($filtros['peso_max']), fn ($query) => $query->where('peso_kg', '<=', (float) $filtros['peso_max']))
            ->with('animal')
            ->orderBy($sortBy, $sortOrder)
            ->paginate($perPage);
    }

    public function obtenerPorId(int $id): Pesaje
    {
        $pesaje = Pesaje::with('animal')->find($id);

        if (!$pesaje) {
            throw new ReglaNegocioException('El pesaje solicitado no existe.', 404);
        }

        return $pesaje;
    }

    public function crearPesaje(array $datos): Pesaje
    {
        return DB::transaction(function () use ($datos) {
            return Pesaje::create($datos);
        });
    }

    public function actualizarPesaje(int $id, array $datos): Pesaje
    {
        $pesaje = $this->obtenerPorId($id);

        DB::transaction(function () use ($pesaje, $datos) {
            $pesaje->update($datos);
        });

        return $pesaje->refresh();
    }

    public function eliminarPesaje(int $id): bool
    {
        $pesaje = $this->obtenerPorId($id);

        return (bool) $pesaje->delete();
    }
}
