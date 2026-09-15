<?php

namespace App\Services;

use App\Exceptions\ReglaNegocioException;
use App\Models\Animal;
use App\Models\Potrero;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class AnimalService
{
    /**
     * Lista >Sanimales aplicando filtros opcionales, delegando en los scopes
     * definidos en el modelo Animal.
     *
     * Filtros soportados: raza_id, potrero_id, sexo, estado.
     */
    public function listarAnimales(array $filtros = []): Collection
    {
        return Animal::query()
            ->when(!empty($filtros['raza_id']), fn ($query) => $query->porRaza((int) $filtros['raza_id']))
            ->when(!empty($filtros['potrero_id']), fn ($query) => $query->porPotrero((int) $filtros['potrero_id']))
            ->when(!empty($filtros['sexo']), fn ($query) => $query->porSexo($filtros['sexo']))
            ->when(!empty($filtros['estado']), fn ($query) => $query->porEstado($filtros['estado']))
            ->with(['raza', 'potrero'])
            ->get();
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
