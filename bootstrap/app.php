<?php

use App\Exceptions\ReglaNegocioException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->booting(function () {
        // Registrar policies para modelos que no se auto-descubren
        // (TratamientoAnimal extiende Pivot en lugar de Model).
        Gate::policy(\App\Models\TratamientoAnimal::class, \App\Policies\TratamientoAnimalPolicy::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // ¿La petición espera JSON? Solo entonces se aplican los formatos de abajo.
        $esApi = fn (Request $request): bool => $request->is('api/*') || $request->expectsJson();

        $exceptions->shouldRenderJsonWhen(fn (Request $request) => $esApi($request));

        // Formato único de error para toda la API:
        //   { "error": "<tipo>", "mensaje": "<texto legible>", ["errors": {...}] }
        // Nunca incluye trazas de pila, rutas de archivos ni consultas SQL.
        $error = static function (string $tipo, string $mensaje, int $estado, array $extra = [], array $cabeceras = []): JsonResponse {
            return response()->json(
                array_merge(['error' => $tipo, 'mensaje' => $mensaje], $extra),
                $estado,
                $cabeceras
            );
        };

        // 1) Regla de negocio -> 409 Conflict (o 404 si el servicio indicó "no existe").
        $exceptions->render(function (ReglaNegocioException $e, Request $request) use ($esApi, $error) {
            if (! $esApi($request)) {
                return null;
            }

            return $e->estadoHttp() === ReglaNegocioException::HTTP_NOT_FOUND
                ? $error('No encontrado', $e->getMessage(), 404)
                : $error('Regla de Negocio', $e->getMessage(), 409);
        });

        // 2) Validación -> 422 con el detalle de errores por campo.
        //    Se mantiene la clave "errors" (mismo nombre que usa Laravel) para
        //    que assertJsonValidationErrors() y los clientes actuales sigan funcionando.
        //    Cuerpo JSON corrupto -> 400: si el Content-Type es JSON pero el
        //    contenido crudo no parsea, no fue un error de validación sino de sintaxis.
        $exceptions->render(function (ValidationException $e, Request $request) use ($esApi, $error) {
            if (! $esApi($request)) {
                return null;
            }

            $crudo = $request->getContent();
            if ($request->isJson() && is_string($crudo) && $crudo !== '' && json_decode($crudo) === null && json_last_error() !== JSON_ERROR_NONE) {
                return $error('Solicitud inválida', 'El cuerpo JSON enviado no es válido.', 400);
            }

            return $error(
                'Validación',
                'Los datos enviados no son válidos.',
                422,
                ['errors' => $e->errors()]
            );
        });

        // 3) Modelo / ruta inexistente -> 404 en JSON limpio.
        //    Laravel convierte ModelNotFoundException en NotFoundHttpException antes
        //    de llegar aquí; la excepción original queda en getPrevious().
        $exceptions->render(function (ModelNotFoundException|NotFoundHttpException $e, Request $request) use ($esApi, $error) {
            if (! $esApi($request)) {
                return null;
            }

            $esModelo = $e instanceof ModelNotFoundException
                || $e->getPrevious() instanceof ModelNotFoundException;

            return $error(
                'No encontrado',
                $esModelo ? 'El recurso solicitado no existe.' : 'La ruta solicitada no existe.',
                404
            );
        });

        // 4) No autenticado -> 401.
        $exceptions->render(function (AuthenticationException $e, Request $request) use ($esApi, $error) {
            if (! $esApi($request)) {
                return null;
            }

            return $error('No autenticado', 'Debe autenticarse para acceder a este recurso.', 401);
        });

        // 4a) No autorizado -> 403 (AuthorizationException de policies/gates).
        $exceptions->render(function (AuthorizationException $e, Request $request) use ($esApi, $error) {
            if (! $esApi($request)) {
                return null;
            }

            return $error('Acceso denegado', 'No tiene permisos para realizar esta acción.', 403);
        });

        // 4b) JSON malformado -> 400 (antes del capturador genérico).
        $exceptions->render(function (JsonException $e, Request $request) use ($esApi, $error) {
            if (! $esApi($request)) {
                return null;
            }

            return $error('Solicitud inválida', 'El cuerpo JSON enviado no es válido.', 400);
        });

        // 5) Cualquier otra cosa (debe registrarse al final: captura Throwable).
        //    - Excepciones HTTP (403, 405, 429, ...) conservan su código.
        //    - Errores imprevistos -> 500 sin stack trace. El detalle real
        //      queda registrado en storage/logs/laravel.log (report() se ejecuta antes).
        $exceptions->render(function (Throwable $e, Request $request) use ($esApi, $error) {
            if (! $esApi($request)) {
                return null;
            }

            // Respuestas ya construidas (p. ej. FormRequest::failedAuthorization) se respetan.
            if ($e instanceof HttpResponseException) {
                return null;
            }

            if ($e instanceof HttpExceptionInterface) {
                $estado = $e->getStatusCode();

                $mensaje = match (true) {
                    $estado === 403 => 'No tiene permisos para realizar esta acción.',
                    $estado === 405 => 'El método HTTP no está permitido para este recurso.',
                    $estado === 429 => 'Demasiadas solicitudes. Intente nuevamente más tarde.',
                    $estado >= 500  => 'Ocurrió un error inesperado. Intente nuevamente más tarde.',
                    default         => $e->getMessage() !== '' ? $e->getMessage() : 'La solicitud no pudo ser procesada.',
                };

                return $error(
                    $estado >= 500 ? 'Error interno' : 'Solicitud inválida',
                    $mensaje,
                    $estado,
                    [],
                    $e->getHeaders()
                );
            }

            return $error(
                'Error interno',
                'Ocurrió un error inesperado. Intente nuevamente más tarde.',
                500
            );
        });
    })->create();
