<?php

namespace App\Services;

use App\Exceptions\ReglaNegocioException;
use App\Models\Animal;
use App\Models\Potrero;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class AnimalService
{
    /**
     * Lista animales aplicando filtros combinables, ordenamiento dinámico
     * y paginación con tope de seguridad.
     *
     * Filtros soportados: raza_id, potrero_id, sexo, estado.
     */
    public function listarAnimales(array $filtros = []): LengthAwarePaginator
    {
        // Paginación segura con tope en 100
        $perPage = min(max((int) ($filtros['per_page'] ?? 15), 1), 100);

        // Ordenamiento por al menos dos campos válidos
        $allowedSortFields = ['numero_arete', 'fecha_nacimiento', 'estado', 'id_animal', 'raza_id', 'potrero_id'];
        $sortBy = in_array($filtros['sort_by'] ?? '', $allowedSortFields, true) ? $filtros['sort_by'] : 'id_animal';
        $sortOrder = strtolower($filtros['order'] ?? 'asc') === 'desc' ? 'desc' : 'asc';

        return Animal::query()
            ->when(!empty($filtros['raza_id']), fn ($query) => $query->porRaza((int) $filtros['raza_id']))
            ->when(!empty($filtros['potrero_id']), fn ($query) => $query->porPotrero((int) $filtros['potrero_id']))
            ->when(!empty($filtros['sexo']), fn ($query) => $query->porSexo($filtros['sexo']))
            ->when(!empty($filtros['estado']), fn ($query) => $query->porEstado($filtros['estado']))
            ->with(['raza', 'potrero'])
            ->orderBy($sortBy, $sortOrder)
            ->paginate($perPage);
    }

    /**
     * Obtiene un animal por su id_animal o lanza una ReglaNegocioException (404)
     * si no existe.
     */
    public function obtenerPorId(int $id): Animal
    {
        $animal = Animal::with(['raza', 'potrero', 'pesajes', 'tratamientos'])->find($id);

        if (!$animal) {
            throw new ReglaNegocioException('El animal solicitado no existe.', 404);
        }

        return $animal;
    }

    /**
     * Verifica que el animal exista sin cargar sus relaciones. Se usa en las
     * rutas anidadas (/animales/{animal}/...) para responder 404 cuando el
     * animal de la URL no existe.
     */
    public function verificarExistencia(int $id): void
    {
        if (!Animal::whereKey($id)->exists()) {
            throw new ReglaNegocioException('El animal solicitado no existe.', 404);
        }
    }

    /**
     * Crea un animal ya validado previamente por StoreAnimalRequest.
     *
     * No asigna un animal a un potrero que ya alcanzó su capacidad máxima.
     */
    public function crearAnimal(array $datos): Animal
    {
        return DB::transaction(function () use ($datos) {
            $this->validarCapacidadPotrero((int) $datos['potrero_id']);

            return Animal::create($datos);
        });
    }

    /**
     * Actualiza un animal ya validado previamente por UpdateAnimalRequest.
     */
    public function actualizarAnimal(int $id, array $datos): Animal
    {
        $animal = $this->obtenerPorId($id);

        DB::transaction(function () use ($animal, $datos) {
            if (isset($datos['potrero_id']) && (int) $datos['potrero_id'] !== (int) $animal->potrero_id) {
                $this->validarCapacidadPotrero((int) $datos['potrero_id']);
            }

            $animal->update($datos);
        });

        return $animal->refresh();
    }

    /**
     * Elimina un animal, aplicando una regla de negocio: no se puede eliminar
     * un animal que ya tiene pesajes registrados (se debe conservar el
     * historial).
     */
    public function eliminarAnimal(int $id): bool
    {
        $animal = $this->obtenerPorId($id);

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
        return DB::transaction(function () use ($datosAnimal, $datosPesaje) {
            $this->validarCapacidadPotrero((int) $datosAnimal['potrero_id']);

            // Crear el animal
            $animal = Animal::create($datosAnimal);

            // Crear el pesaje asociado al animal recién creado.
            // Si esto falla, DB::transaction revierte también el animal.
            $animal->pesajes()->create($datosPesaje);

            return $animal;
        });
    }

    /*
     * El número de animales del potrero no puede superar su
     * capacidad máxima
     */
    private function validarCapacidadPotrero(int $potreroId): void
    {
        $potrero = Potrero::find($potreroId);

        if (!$potrero) {
            throw new ReglaNegocioException('El potrero seleccionado no existe.', 422);
        }


        $ocupados = $potrero->animales()->count();

        if ($ocupados >= (int) $potrero->capacidad_maxima) {
            throw new ReglaNegocioException(
                'El potrero seleccionado ya alcanzó su capacidad máxima.',
                422
            );
        }
    }
}
