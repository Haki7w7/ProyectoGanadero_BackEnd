<?php

namespace App\Services;

use App\Exceptions\ReglaNegocioException;
use App\Models\Potrero;
use App\Models\PuntoRonda;
use App\Models\Ronda;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class RondaService
{
    /**
     * Lista las rondas registradas.
     * Si el usuario es Operario, retorna únicamente sus propias rondas.
     * Si es Administrador, retorna el historial global cargando la relación con el operador.
     */
    public function listarRondas(User $usuario, array $filtros = []): LengthAwarePaginator
    {
        Gate::forUser($usuario)->authorize('viewAny', Ronda::class); // CAPA 2

        $perPage = min(max((int) ($filtros['per_page'] ?? 15), 1), 100);

        $query = Ronda::query();

        if ($usuario->isAdmin()) {
            $query->with(['user', 'puntosRonda.potrero'])->withCount('puntosRonda');
        } else {
            $query->where('user_id', $usuario->id)
                ->with(['puntosRonda.potrero'])
                ->withCount('puntosRonda');
        }

        if (!empty($filtros['estado'])) {
            $query->where('estado', $filtros['estado']);
        }

        return $query->orderBy('id', 'desc')->paginate($perPage);
    }

    /**
     * Obtiene una ronda por su identificador.
     */
    public function obtenerPorId(int $id, ?User $usuario = null): Ronda
    {
        $ronda = Ronda::with(['user', 'puntosRonda.potrero'])->find($id);

        if (!$ronda) {
            throw new ReglaNegocioException('La ronda solicitada no existe.', 404);
        }

        if ($usuario) {
            Gate::forUser($usuario)->authorize('view', $ronda); // CAPA 2
        }

        return $ronda;
    }

    /**
     * Inicia una nueva ronda para el operario autenticado.
     */
    public function iniciarRonda(User $usuario, array $datos = []): Ronda
    {
        Gate::forUser($usuario)->authorize('create', Ronda::class); // CAPA 2

        // Regla de Negocio: No permitir iniciar otra ronda si el operario ya tiene una activa
        $rondaActiva = Ronda::where('user_id', $usuario->id)
            ->where('estado', 'en_progreso')
            ->first();

        if ($rondaActiva) {
            throw new ReglaNegocioException(
                "Ya tiene una ronda en progreso (ID: {$rondaActiva->id}). Debe finalizarla antes de iniciar una nueva.",
                409
            );
        }

        return DB::transaction(function () use ($usuario, $datos) {
            return Ronda::create([
                'user_id'       => $usuario->id,
                'fecha_inicio'  => now(),
                'estado'        => 'en_progreso',
                'observaciones' => $datos['observaciones'] ?? null,
            ]);
        });
    }

    /**
     * Registra un punto de control en una ronda en progreso.
     * Valida que la ronda no esté completada y que el potrero no se repita.
     */
    public function registrarPunto(int $rondaId, array $datos, User $usuario): PuntoRonda
    {
        $ronda = $this->obtenerPorId($rondaId);

        Gate::forUser($usuario)->authorize('update', $ronda); // CAPA 2

        // Regla RN: La ronda no puede estar completada
        if ($ronda->estado === 'completada') {
            throw new ReglaNegocioException(
                'No se pueden registrar puntos en una ronda que ya ha sido completada.',
                409
            );
        }

        // Buscar el potrero mediante su código QR
        $potrero = Potrero::where('codigo_qr', $datos['codigo_qr'])->first();

        if (!$potrero) {
            throw new ReglaNegocioException('El potrero con el código QR especificado no existe.', 404);
        }

        // Regla RN: El potrero no puede repetirse en la misma ronda
        $yaRegistrado = $ronda->puntosRonda()
            ->where('potrero_id', $potrero->potrero_id)
            ->exists();

        if ($yaRegistrado) {
            throw new ReglaNegocioException(
                'El potrero ya ha sido registrado en esta ronda de inspección.',
                409
            );
        }

        return DB::transaction(function () use ($ronda, $potrero, $datos) {
            $punto = $ronda->puntosRonda()->create([
                'potrero_id'     => $potrero->potrero_id,
                'latitud'        => $datos['latitud'],
                'longitud'       => $datos['longitud'],
                'estado_potrero' => $datos['estado_potrero'],
                'observacion'    => $datos['observacion'] ?? null,
                'registrado_en'  => now(),
            ]);

            return $punto->load('potrero');
        });
    }

    /**
     * Finaliza una ronda de inspección activa.
     */
    public function finalizarRonda(int $rondaId, array $datos, User $usuario): Ronda
    {
        $ronda = $this->obtenerPorId($rondaId);

        Gate::forUser($usuario)->authorize('update', $ronda); // CAPA 2

        if ($ronda->estado === 'completada') {
            throw new ReglaNegocioException('La ronda ya se encuentra finalizada.', 409);
        }

        return DB::transaction(function () use ($ronda, $datos) {
            $ronda->update([
                'estado'        => 'completada',
                'fecha_fin'     => now(),
                'observaciones' => $datos['observaciones'] ?? $ronda->observaciones,
            ]);

            return $ronda->fresh(['user', 'puntosRonda.potrero']);
        });
    }
}

