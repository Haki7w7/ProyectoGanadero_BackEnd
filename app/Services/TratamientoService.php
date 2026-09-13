<?php

namespace App\Services;

use App\Exceptions\ReglaNegocioException;
use App\Models\Tratamiento;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class TratamientoService
{
    /**
     * Lista tratamientos aplicando filtro opcional por tipo, delegando
     * en el scope definido en el modelo Tratamiento.
     */
    public function listarTratamientos(array $filtros = []): Collection
    {
        return Tratamiento::query()
            ->when(!empty($filtros['tipo']), fn ($query) => $query->porTipo($filtros['tipo']))
            ->get();
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
