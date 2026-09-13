<?php

namespace App\Services;

use App\Exceptions\ReglaNegocioException;
use App\Models\Raza;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class RazaService
{
    public function listarRazas(array $filtros = []): Collection
    {
        return Raza::query()->get();
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
