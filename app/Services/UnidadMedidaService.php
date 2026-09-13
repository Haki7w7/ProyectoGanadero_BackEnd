<?php

namespace App\Services;

use App\Exceptions\ReglaNegocioException;
use App\Models\UnidadMedida;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class UnidadMedidaService
{
    public function listarUnidadesMedida(array $filtros = []): Collection
    {
        return UnidadMedida::query()->get();
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
