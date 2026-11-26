<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

#[Group('Autenticación')]
class RegisterController extends Controller
{
    public function __invoke(RegisterRequest $request): JsonResponse
    {
        if (! $request->user()?->hasRole('admin')) {
            return response()->json([
                'error' => 'No autorizado',
                'mensaje' => 'Solo un administrador puede registrar nuevos usuarios.',
            ], 403);
        }

        $rol = $this->resolverRol($request->role);

        $usuario = User::create([
            'name' => $request->name,
            'email' => Str::lower($request->email),
            'password' => Hash::make($request->password),
        ]);

        $usuario->assignRole($rol);

        return response()->json([
            'message' => 'Usuario registrado exitosamente.',
            'usuario' => [
                'id' => $usuario->id,
                'name' => $usuario->name,
                'email' => $usuario->email,
                'role' => $rol,
            ],
        ], 201);
    }

    private function resolverRol(string $rol): string
    {
        Role::findOrCreate($rol, 'web');

        return $rol;
    }
}
