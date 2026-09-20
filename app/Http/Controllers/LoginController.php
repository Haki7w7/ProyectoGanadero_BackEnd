<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

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
     * @response 422 {
     *   "message": "Las credenciales proporcionadas son incorrectas.",
     *   "errors": {
     *     "email": ["Las credenciales proporcionadas son incorrectas."]
     *   }
     * }
     */
    public function __invoke(LoginRequest $request): JsonResponse
    {
        $usuario = User::where('email', $request->email)->first();

        if (! $usuario || ! Hash::check($request->password, $usuario->password)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales proporcionadas son incorrectas.'],
            ]);
        }

        $token = $usuario->createToken('auth-token');

        return response()->json([
            'message' => 'Autenticación exitosa.',
            'token'   => $token->plainTextToken,
        ], 200);
    }
}
