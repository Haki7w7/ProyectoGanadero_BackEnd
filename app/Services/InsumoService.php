<?php

namespace App\Services;

use App\Exceptions\ReglaNegocioException;
use App\Models\Insumo;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class InsumoService
{
    /**
     * Lista insumos aplicando filtros opcionales, delegando en los scopes
     * definidos en el modelo Insumo.
     *
     * Filtros soportados: categoria_id, unidad_medida_id.
     */
    public function listarInsumos(array $filtros = []): Collection
    {
        return Insumo::query()
            ->when(!empty($filtros['categoria_id']), fn ($query) => $query->porCategoria((int) $filtros['categoria_id']))
            ->when(!empty($filtros['unidad_medida_id']), fn ($query) => $query->porUnidadMedida((int) $filtros['unidad_medida_id']))
            ->with(['categoria', 'unidadMedida'])
            ->get();
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
