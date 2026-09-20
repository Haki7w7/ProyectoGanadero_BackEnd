<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

/**
 * @tags Autenticación
 */
class RegisterController extends Controller
{
    /**
     * Registrar usuario.
     *
     * Registra un nuevo usuario con rol asignado (admin, operario, veterinario) y emite un token Bearer.
     *
     * @response 201 {
     *   "message": "Usuario registrado exitosamente.",
     *   "token": "2|SDucxGoM2IQbyRKDu6K806lIQVS3CSLxneAljzG8bcd65ed8",
     *   "usuario": {
     *     "id": 1,
     *     "name": "Dra. Laura Gómez",
     *     "email": "laura@guateganado.cr",
     *     "role": "veterinario"
     *   }
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
        $usuario = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => $request->role,
        ]);

        $token = $usuario->createToken('auth-token');

        return response()->json([
            'message' => 'Usuario registrado exitosamente.',
            'token'   => $token->plainTextToken,
            'usuario' => [
                'id'    => $usuario->id,
                'name'  => $usuario->name,
                'email' => $usuario->email,
                'role'  => $usuario->role,
            ],
        ], 201);
    }
}
