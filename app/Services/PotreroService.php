<?php

namespace App\Services;

use App\Exceptions\ReglaNegocioException;
use App\Models\Potrero;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class PotreroService
{
    public function listarPotreros(array $filtros = []): LengthAwarePaginator
    {
        $perPage = min (max((int) ($filtros['per_page'] ?? 15), 1), 100);

        $allowedSortFields = ['nombre', 'hectareas_de_extension', 'capacidad_maxima', 'potrero_id'];
        $sortBy = in_array($filtros['sort_by'] ?? '', $allowedSortFields, true) ? $filtros['sort_by'] : 'potrero_id';
        $sortOrder = strtolower($filtros['order'] ?? 'asc') === 'desc' ? 'desc' : 'asc';

        return Potrero::query()
            ->withCount(['animales as ocupacion_actual' => fn ($q) => $q->where('estado', '!=', 'Vendido')])
            ->when(!empty($filtros['estado_pasto']), fn ($query) => $query->where('estado_pasto', $filtros['estado_pasto']))
            ->when(!empty($filtros['nombre']), fn ($query) => $query->where('nombre', 'like', '%' . $filtros['nombre'] . '%'))
            ->orderBy($sortBy, $sortOrder)
            ->paginate($perPage);
    }

    public function obtenerPorId(int $id): Potrero
    {
        $potrero = Potrero::query()
            ->withCount(['animales as ocupacion_actual' => fn ($q) => $q->where('estado', '!=', 'Vendido')])
            ->find($id);

        if (!$potrero) {
            throw new ReglaNegocioException('El potrero solicitado no existe.', 404);
        }

        return $potrero;
    }

    public function crearPotrero(array $datos): Potrero
    {
        Gate::authorize('create', Potrero::class);

        if (empty($datos['codigo_qr'])) {
            $datos['codigo_qr'] = 'POTRERO-' . strtoupper(Str::slug($datos['nombre']));
        }

        $potrero = DB::transaction(function () use ($datos) {
            return Potrero::create($datos);
        });

        return $potrero->loadCount([
            'animales as ocupacion_actual' => fn ($q) => $q->where('estado', '!=', 'Vendido'),
        ]);
    }

    public function actualizarPotrero(int $id, array $datos): Potrero
    {
        $potrero = $this->obtenerPorId($id);

        Gate::authorize('update', $potrero);

        if (empty($datos['codigo_qr']) && (array_key_exists('codigo_qr', $datos) || empty($potrero->codigo_qr))) {
            $nombre = $datos['nombre'] ?? $potrero->nombre;
            $datos['codigo_qr'] = 'POTRERO-' . strtoupper(Str::slug($nombre));
        }

        DB::transaction(function () use ($potrero, $datos) {
            $potreroBloqueado = Potrero::where('potrero_id', $potrero->potrero_id)
                ->lockForUpdate()
                ->first();

            if (!$potreroBloqueado) {
                throw new ReglaNegocioException('El potrero solicitado no existe.', ReglaNegocioException::HTTP_NOT_FOUND);
            }

            if (array_key_exists('capacidad_maxima', $datos)) {
                $ocupacionActual = $potreroBloqueado->animales()
                    ->where('estado', '!=', 'Vendido')
                    ->count();

                if ((int) $datos['capacidad_maxima'] < $ocupacionActual) {
                    throw new ReglaNegocioException(
                        "No se puede reducir la capacidad máxima a {$datos['capacidad_maxima']} porque el potrero tiene actualmente {$ocupacionActual} animales ocupando cupo.",
                        ReglaNegocioException::HTTP_CONFLICT
                    );
                }
            }

            $potreroBloqueado->update($datos);
        });

        return $potrero->refresh()->loadCount([
            'animales as ocupacion_actual' => fn ($q) => $q->where('estado', '!=', 'Vendido'),
        ]);
    }

    public function eliminarPotrero(int $id): bool
    {
        $potrero = $this->obtenerPorId($id);

        Gate::authorize('delete', $potrero);

        if ($potrero->animales()->exists()) {
            throw new ReglaNegocioException(
                'No se puede eliminar el potrero porque tiene animales asignados actualmente.',
                422
            );
        }

        return (bool) $potrero->delete();
    }
}
