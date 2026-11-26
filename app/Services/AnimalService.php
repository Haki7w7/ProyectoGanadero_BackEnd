<?php

namespace App\Services;

use App\Exceptions\ReglaNegocioException;
use App\Models\Animal;
use App\Models\Potrero;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class AnimalService
{
    public function listarAnimales(array $filtros = []): LengthAwarePaginator
    {
        $perPage = min(max((int) ($filtros['per_page'] ?? 15), 1), 100);

        $allowedSortFields = ['numero_arete', 'fecha_nacimiento', 'estado', 'id_animal', 'raza_id', 'potrero_id'];
        $sortBy = in_array($filtros['sort_by'] ?? '', $allowedSortFields, true) ? $filtros['sort_by'] : 'id_animal';
        $sortOrder = strtolower($filtros['order'] ?? 'asc') === 'desc' ? 'desc' : 'asc';

        return Animal::query()
            ->when(!empty($filtros['raza_id']), fn ($query) => $query->porRaza((int) $filtros['raza_id']))
            ->when(!empty($filtros['potrero_id']), fn ($query) => $query->porPotrero((int) $filtros['potrero_id']))
            ->when(!empty($filtros['sexo']), fn ($query) => $query->porSexo($filtros['sexo']))
            ->when(!empty($filtros['estado']), fn ($query) => $query->porEstado($filtros['estado']))
            ->when(is_string($filtros['search'] ?? null) && trim($filtros['search']) !== '', fn ($query) => $query->buscar($filtros['search']))
            ->with(['raza', 'potrero'])
            ->orderBy($sortBy, $sortOrder)
            ->paginate($perPage);
    }

    public function obtenerPorId(int $id): Animal
    {
        $animal = Animal::with(['raza', 'potrero', 'pesajes', 'tratamientos'])->find($id);

        if (!$animal) {
            throw new ReglaNegocioException('El animal solicitado no existe.', 404);
        }

        return $animal;
    }

    public function obtenerPorCodigoQr(string $codigoQr): Animal
    {
        $animal = Animal::with(['raza', 'potrero', 'pesajes', 'tratamientos'])
            ->where('codigo_qr', $codigoQr)
            ->first();

        if (!$animal) {
            throw new ReglaNegocioException('El animal con el código QR especificado no existe.', 404);
        }

        return $animal;
    }

    public function verificarExistencia(int $id): void
    {
        if (!Animal::whereKey($id)->exists()) {
            throw new ReglaNegocioException('El animal solicitado no existe.', 404);
        }
    }

    public function crearAnimal(array $datos): Animal
    {
        Gate::authorize('create', Animal::class);

        if (empty($datos['codigo_qr'])) {
            $datos['codigo_qr'] = 'VACA-' . $datos['numero_arete'];
        }

        return DB::transaction(function () use ($datos) {
            $estadoNuevo = $datos['estado'] ?? 'Activo';

            if ($estadoNuevo !== 'Vendido') {
                $this->validarCapacidadPotrero((int) $datos['potrero_id']);
            }

            return Animal::create($datos);
        });
    }

    public function actualizarAnimal(int $id, array $datos): Animal
    {
        $animal = $this->obtenerPorId($id);

        Gate::authorize('update', $animal);

        if (empty($datos['codigo_qr']) && (array_key_exists('codigo_qr', $datos) || empty($animal->codigo_qr))) {
            $arete = $datos['numero_arete'] ?? $animal->numero_arete;
            $datos['codigo_qr'] = 'VACA-' . $arete;
        }

        DB::transaction(function () use ($animal, $datos) {
            $potreroDestinoId = (int) ($datos['potrero_id'] ?? $animal->potrero_id);
            $cambiaPotrero = $potreroDestinoId !== (int) $animal->potrero_id;
            $estadoFinal = array_key_exists('estado', $datos) && $datos['estado'] !== null
                ? $datos['estado']
                : $animal->estado;
            $reactivaDesdeVendido = $animal->estado === 'Vendido' && $estadoFinal !== 'Vendido';

            if ($estadoFinal !== 'Vendido' && ($cambiaPotrero || $reactivaDesdeVendido)) {
                $this->validarCapacidadPotrero($potreroDestinoId);
            }

            $animal->update($datos);
        });

        return $animal->refresh();
    }

    public function eliminarAnimal(int $id): bool
    {
        $animal = $this->obtenerPorId($id);

        Gate::authorize('delete', $animal);

        if ($animal->pesajes()->exists()) {
            throw new ReglaNegocioException(
                'No se puede eliminar el animal porque tiene pesajes registrados en su historial.',
                422
            );
        }

        return (bool) $animal->delete();
    }

    public function registrarAnimalConPesaje(array $datosAnimal, array $datosPesaje): Animal
    {
        if (empty($datosAnimal['codigo_qr']) && !empty($datosAnimal['numero_arete'])) {
            $datosAnimal['codigo_qr'] = 'VACA-' . $datosAnimal['numero_arete'];
        }

        return DB::transaction(function () use ($datosAnimal, $datosPesaje) {
            $estadoNuevo = $datosAnimal['estado'] ?? 'Activo';

            if ($estadoNuevo !== 'Vendido') {
                $this->validarCapacidadPotrero((int) $datosAnimal['potrero_id']);
            }

            $animal = Animal::create($datosAnimal);

            $animal->pesajes()->create($datosPesaje);

            return $animal;
        });
    }

    private function validarCapacidadPotrero(int $potreroId): void
    {
        $potrero = Potrero::where('potrero_id', $potreroId)->lockForUpdate()->first();

        if (!$potrero) {
            throw new ReglaNegocioException('El potrero seleccionado no existe.', ReglaNegocioException::HTTP_CONFLICT);
        }

        $ocupados = $potrero->animales()
            ->where('estado', '!=', 'Vendido')
            ->count();

        if ($ocupados >= (int) $potrero->capacidad_maxima) {
            throw new ReglaNegocioException(
                'El potrero seleccionado ya alcanzó su capacidad máxima.',
                ReglaNegocioException::HTTP_CONFLICT
            );
        }
    }
}
