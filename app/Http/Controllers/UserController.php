<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

#[Group('Usuarios')]
class UserController extends Controller
{
    /**
     * Listar usuarios.
     *
     * Retorna la lista de usuarios registrados ordenados por ID ascendente con su rol asignado.
     * Exclusivo para administradores.
     */
    public function index(Request $request): AnonymousResourceCollection|JsonResponse
    {
        if (! $request->user()?->hasRole('admin')) {
            return response()->json([
                'error'   => 'No autorizado',
                'mensaje' => 'Solo un administrador puede consultar la lista de usuarios.',
            ], 403);
        }

        $usuarios = User::with('roles')->orderBy('id', 'asc')->get();

        return UserResource::collection($usuarios);
    }
}
