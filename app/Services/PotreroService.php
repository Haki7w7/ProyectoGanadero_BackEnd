<?php

namespace App\Services;

use App\Exceptions\ReglaNegocioException;
use App\Models\Potrero;
use Illuminate\Contracts\Pagination\LengthAwarePaginator; // Cambiar Collection por esto
use Illuminate\Support\Facades\DB;

class PotreroService
{
    /**
     * Lista potreros aplicando filtro opcional por estado_pasto,
     * delegando en el scope definido en el modelo Potrero.
     */
    /**
     * Lista potreros aplicando filtros combinables, ordenamiento dinámico
     * y paginación con tope de seguridad de memoria.
     */
    public function listarPotreros(array $filtros = []): LengthAwarePaginator
    {
        // Paginación segura con tope en 100
        $perPage = min((int) ($filtros['per_page'] ?? 15), 100);

        // Ordenamiento por al menos dos campos válidos
        $allowedSortFields = ['nombre', 'hectareas_de_extension', 'capacidad_maxima', 'potrero_id'];
        $sortBy = in_array($filtros['sort_by'] ?? '', $allowedSortFields, true) ? $filtros['sort_by'] : 'potrero_id';
        $sortOrder = strtolower($filtros['order'] ?? 'asc') === 'desc' ? 'desc' : 'asc';

        return Potrero::query()
            ->when(!empty($filtros['estado_pasto']), fn ($query) => $query->where('estado_pasto', $filtros['estado_pasto']))
            ->when(!empty($filtros['nombre']), fn ($query) => $query->where('nombre', 'like', '%' . $filtros['nombre'] . '%'))
            ->orderBy($sortBy, $sortOrder)
            ->paginate($perPage);
    }

    /**
     * Obtiene un potrero por su potrero_id o lanza una ReglaNegocioException (404)
     * si no existe.
     */
    public function obtenerPorId(int $id): Potrero
    {
        $potrero = Potrero::find($id);

        if (!$potrero) {
            throw new ReglaNegocioException('El potrero solicitado no existe.', 404);
        }

        return $potrero;
    }

    /**
     * Crea un potrero ya validado previamente por StorePotreroRequest.
     */
    public function crearPotrero(array $datos): Potrero
    {
        return DB::transaction(function () use ($datos) {
            return Potrero::create($datos);
        });
    }

    /**
     * Actualiza un potrero ya validado previamente por UpdatePotreroRequest.
     */
    public function actualizarPotrero(int $id, array $datos): Potrero
    {
        $potrero = $this->obtenerPorId($id);

        DB::transaction(function () use ($potrero, $datos) {
            $potrero->update($datos);
        });

        return $potrero->refresh();
    }

    /**
     * Elimina un potrero, aplicando una regla de negocio: no se puede eliminar
     * un potrero que todavía tiene animales asignados.
     */
    public function eliminarPotrero(int $id): bool
    {
        $potrero = $this->obtenerPorId($id);

        if ($potrero->animales()->exists()) {
            throw new ReglaNegocioException(
                'No se puede eliminar el potrero porque tiene animales asignados actualmente.',
                422
            );
        }

        return (bool) $potrero->delete();
    }
}
