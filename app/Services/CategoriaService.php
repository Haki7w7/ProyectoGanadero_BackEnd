<?php

namespace App\Services;

use App\Exceptions\ReglaNegocioException;
use App\Models\Categoria;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class CategoriaService
{
    /**
     * Lista categorías aplicando filtros combinables, ordenamiento dinámico
     * y paginación con tope de seguridad.
     */
    public function listarCategorias(array $filtros = []): LengthAwarePaginator
    {
        // Paginación segura con tope en 100
        $perPage = min(max((int) ($filtros['per_page'] ?? 15), 1), 100);

        // Ordenamiento por al menos dos campos válidos
        $allowedSortFields = ['nombre', 'tipo', 'categoria_id'];
        $sortBy = in_array($filtros['sort_by'] ?? '', $allowedSortFields, true) ? $filtros['sort_by'] : 'categoria_id';
        $sortOrder = strtolower($filtros['order'] ?? 'asc') === 'desc' ? 'desc' : 'asc';

        return Categoria::query()
            ->when(!empty($filtros['tipo']), fn ($query) => $query->where('tipo', $filtros['tipo']))
            ->when(!empty($filtros['buscar'] ?? $filtros['nombre'] ?? null), function ($query) use ($filtros) {
                $termino = $filtros['buscar'] ?? $filtros['nombre'];
                $query->where('nombre', 'like', '%' . $termino . '%');
            })
            ->orderBy($sortBy, $sortOrder)
            ->paginate($perPage);
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
