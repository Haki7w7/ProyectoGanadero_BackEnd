<?php

namespace App\Services;

use App\Exceptions\ReglaNegocioException;
use App\Models\Categoria;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class CategoriaService
{
    /**
     * Lista categorías aplicando filtro opcional por tipo, delegando
     * en el scope definido en el modelo Categoria.
     */
    public function listarCategorias(array $filtros = []): Collection
    {
        return Categoria::query()
            ->when(!empty($filtros['tipo']), fn ($query) => $query->porTipo($filtros['tipo']))
            ->get();
    }

    public function obtenerPorId(int $id): Categoria
    {
        $categoria = Categoria::find($id);

        if (!$categoria) {
            throw new ReglaNegocioException('La categoría solicitada no existe.', 404);
        }

        return $categoria;
    }

    public function crearCategoria(array $datos): Categoria
    {
        return DB::transaction(function () use ($datos) {
            return Categoria::create($datos);
        });
    }

    public function actualizarCategoria(int $id, array $datos): Categoria
    {
        $categoria = $this->obtenerPorId($id);

        DB::transaction(function () use ($categoria, $datos) {
            $categoria->update($datos);
        });

        return $categoria->refresh();
    }

    /**
     * Elimina una categoría, aplicando una regla de negocio: no se puede
     * eliminar una categoría que tiene insumos asociados.
     */
    public function eliminarCategoria(int $id): bool
    {
        $categoria = $this->obtenerPorId($id);

        if ($categoria->insumos()->exists()) {
            throw new ReglaNegocioException(
                'No se puede eliminar la categoría porque tiene insumos asociados.',
                422
            );
        }

        return (bool) $categoria->delete();
    }
}
