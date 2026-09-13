<?php

namespace App\Services;

use App\Exceptions\ReglaNegocioException;
use App\Models\Pesaje;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class PesajeService
{
    /**
     * Lista pesajes aplicando filtro opcional por animal, delegando en el
     * scope definido en el modelo Pesaje.
     */
    public function listarPesajes(array $filtros = []): Collection
    {
        return Pesaje::query()
            ->when(!empty($filtros['id_animal']), fn ($query) => $query->porAnimal((int) $filtros['id_animal']))
            ->with('animal')
            ->get();
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
