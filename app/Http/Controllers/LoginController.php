<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

use Dedoc\Scramble\Attributes\Group;

#[Group('Autenticación')]
class LoginController extends Controller
{
    /**
     * Iniciar sesión.
     *
     * Valida las credenciales del usuario y genera un token de acceso personal (Bearer Token).
     *
     * @response 200 {
     *   "message": "Autenticación exitosa.",
     *   "token": "1|bJmclCitAI1H5P4Op6d8JIA5H3fi6zf4..."
     * }
     * @response 401 {
     *   "error": "No autenticado",
     *   "mensaje": "Las credenciales proporcionadas son incorrectas."
     * }
     * @response 429 {
     *   "error": "Demasiadas solicitudes",
     *   "mensaje": "Demasiadas solicitudes. Intente nuevamente más tarde."
     * }
     */
    public function __invoke(LoginRequest $request): JsonResponse
    {
        $clave = Str::lower($request->email) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($clave, 5)) {
            return response()->json([
                'error'   => 'Demasiadas solicitudes',
                'mensaje' => 'Demasiadas solicitudes. Intente nuevamente más tarde.',
            ], 429);
        }

        $usuario = User::where('email', $request->email)->first();

        if (! $usuario || ! Hash::check($request->password, $usuario->password)) {
            RateLimiter::hit($clave);

            return response()->json([
                'error'   => 'No autenticado',
                'mensaje' => 'Las credenciales proporcionadas son incorrectas.',
            ], 401);
        }

        RateLimiter::clear($clave);

        $token = $usuario->createToken('auth-token', $usuario->role->abilities());

        return response()->json([
            'message' => 'Autenticación exitosa.',
            'token'   => $token->plainTextToken,
        ], 200);
    }

    // Definición de habilidades según el rol del usuario
    $permisos = $usuario->rol === 'admin' ? ['*'] : ['animales:read', 'animales:create'];

    // Creación del token de Sanctum
    $token = $usuario->createToken('auth_token', $permisos)->plainTextToken;

    return response()->json([
        'message' => 'Autenticación exitosa.',
        'token'   => $token, // Se pasa $token directamente ya que es el string generado
        'user'    => [
            'id'    => $usuario->id,
            'name'  => $usuario->name,
            'email' => $usuario->email,
            'rol'   => $usuario->rol,
        ],
    ], 200);
}
}