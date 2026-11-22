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
    /**
     * Registrar usuario (solo Administrador).
     *
     * Registra un nuevo usuario con rol asignado (admin, operario, veterinario).
     * No emite token de acceso para no interferir con la sesión del administrador autenticado.
     *
     * @response 201 {
     *   "message": "Usuario registrado exitosamente.",
     *   "usuario": {
     *     "id": 2,
     *     "name": "Dra. Laura Gómez",
     *     "email": "laura@guateganado.cr",
     *     "role": "veterinario"
     *   }
     * }
     * @response 403 {
     *   "error": "No autorizado",
     *   "mensaje": "Solo un administrador puede registrar nuevos usuarios."
     * }
     * @response 422 {
     *   "message": "Los datos proporcionados no son válidos.",
     *   "errors": {
     *     "email": ["Este correo electrónico ya se encuentra registrado."],
     *     "role": ["El rol seleccionado no es válido. Debe ser admin, operario o veterinario."]
     *   }
     * }
     */
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

    /**
     * Garantiza que el rol exista antes de asignarlo.
     *
     * assignRole() lanza RoleDoesNotExist si el rol no está en la base de datos,
     * lo que devolvía 500 en toda instalación donde no se hubiera corrido el
     * seeder. findOrCreate mantiene el registro disponible sin depender de que
     * alguien haya precargado los roles.
     */
    private function resolverRol(string $rol): string
    {
        Role::findOrCreate($rol, 'web');

        return $rol;
    }
}
