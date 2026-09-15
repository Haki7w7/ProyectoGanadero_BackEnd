<?php

namespace App\Services;

use App\Exceptions\ReglaNegocioException;
use App\Models\Raza;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class RazaService
{
    /**
     * Lista razas aplicando filtros combinables, ordenamiento dinámico
     * y paginación con tope de seguridad.
     */
    public function listarRazas(array $filtros = []): LengthAwarePaginator
    {
        // Paginación segura con tope en 100
        $perPage = min(max((int) ($filtros['per_page'] ?? 15), 1), 100);

        // Ordenamiento por al menos dos campos válidos
        $allowedSortFields = ['nombre', 'raza_id', 'created_at'];
        $sortBy = in_array($filtros['sort_by'] ?? '', $allowedSortFields, true) ? $filtros['sort_by'] : 'raza_id';
        $sortOrder = strtolower($filtros['order'] ?? 'asc') === 'desc' ? 'desc' : 'asc';

        return Raza::query()
            ->when(!empty($filtros['buscar'] ?? $filtros['nombre'] ?? null), function ($query) use ($filtros) {
                $termino = $filtros['buscar'] ?? $filtros['nombre'];
                $query->where('nombre', 'like', '%' . $termino . '%');
            })
            ->orderBy($sortBy, $sortOrder)
            ->paginate($perPage);
    }

    public function obtenerPorId(int $id): Raza
    {
        $raza = Raza::find($id);

        if (!$raza) {
            throw new ReglaNegocioException('La raza solicitada no existe.', 404);
        }

        return $raza;
    }

    public function crearRaza(array $datos): Raza
    {
        return DB::transaction(function () use ($datos) {
            return Raza::create($datos);
        });
    }

    public function actualizarRaza(int $id, array $datos): Raza
    {
        $raza = $this->obtenerPorId($id);

        DB::transaction(function () use ($raza, $datos) {
            $raza->update($datos);
        });

        return $raza->refresh();
    }

    /**
     * Elimina una raza, aplicando una regla de negocio: no se puede
     * eliminar si tiene animales asociados.
     */
    public function eliminarRaza(int $id): bool
    {
        $raza = $this->obtenerPorId($id);

        if ($raza->animales()->exists()) {
            throw new ReglaNegocioException(
                'No se puede eliminar la raza porque tiene animales asociados.',
                422
            );
        }

        return (bool) $raza->delete();
    }
}
