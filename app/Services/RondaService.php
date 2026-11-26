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
    public function listarRondas(User $usuario, array $filtros = []): LengthAwarePaginator
    {
        Gate::forUser($usuario)->authorize('viewAny', Ronda::class);

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

    public function obtenerPorId(int $id, ?User $usuario = null): Ronda
    {
        $ronda = Ronda::with(['user', 'puntosRonda.potrero'])->find($id);

        if (!$ronda) {
            throw new ReglaNegocioException('La ronda solicitada no existe.', 404);
        }

        if ($usuario) {
            Gate::forUser($usuario)->authorize('view', $ronda);
        }

        return $ronda;
    }

    public function iniciarRonda(User $usuario, array $datos = []): Ronda
    {
        Gate::forUser($usuario)->authorize('create', Ronda::class);

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

    public function registrarPunto(int $rondaId, array $datos, User $usuario): PuntoRonda
    {
        $ronda = $this->obtenerPorId($rondaId);

        Gate::forUser($usuario)->authorize('update', $ronda);

        if ($ronda->estado === 'completada') {
            throw new ReglaNegocioException(
                'No se pueden registrar puntos en una ronda que ya ha sido completada.',
                409
            );
        }

        $potrero = Potrero::where('codigo_qr', $datos['codigo_qr'])->first();

        if (!$potrero) {
            throw new ReglaNegocioException('El potrero con el código QR especificado no existe.', 404);
        }

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

    public function finalizarRonda(int $rondaId, array $datos, User $usuario): Ronda
    {
        $ronda = $this->obtenerPorId($rondaId);

        Gate::forUser($usuario)->authorize('update', $ronda);

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

