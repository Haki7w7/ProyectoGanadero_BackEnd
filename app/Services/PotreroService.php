<?php

namespace App\Services;

use App\Exceptions\ReglaNegocioException;
use App\Models\Potrero;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class PotreroService
{
    /**
     * Lista potreros aplicando filtro opcional por estado_pasto,
     * delegando en el scope definido en el modelo Potrero.
     */
    public function listarPotreros(array $filtros = []): Collection
    {
        return Potrero::query()
            ->when(
                !empty($filtros['estado_pasto']),
                fn ($query) => $query->porEstadoPasto($filtros['estado_pasto'])
            )
            ->get();
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
