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
        $middleware->trustProxies(at: '*');

        $middleware->redirectGuestsTo(
            fn (Request $request) => $request->is('api/*') ? null : '/login'
        );
    })
    ->booting(function () {
        Gate::policy(\App\Models\TratamientoAnimal::class, \App\Policies\TratamientoAnimalPolicy::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $esApi = fn (Request $request): bool => $request->is('api/*') || $request->expectsJson();

        $exceptions->shouldRenderJsonWhen(fn (Request $request) => $esApi($request));

        $error = static function (string $tipo, string $mensaje, int $estado, array $extra = [], array $cabeceras = []): JsonResponse {
            return response()->json(
                array_merge(['error' => $tipo, 'mensaje' => $mensaje], $extra),
                $estado,
                $cabeceras
            );
        };

        $exceptions->render(function (ReglaNegocioException $e, Request $request) use ($esApi, $error) {
            if (! $esApi($request)) {
                return null;
            }

            return $e->estadoHttp() === ReglaNegocioException::HTTP_NOT_FOUND
                ? $error('No encontrado', $e->getMessage(), 404)
                : $error('Regla de Negocio', $e->getMessage(), 409);
        });

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

        $exceptions->render(function (AuthenticationException $e, Request $request) use ($esApi, $error) {
            if (! $esApi($request)) {
                return null;
            }

            return $error('No autenticado', 'Debe autenticarse para acceder a este recurso.', 401);
        });

        $exceptions->render(function (AuthorizationException|\Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException $e, Request $request) use ($esApi, $error) {
            if (! $esApi($request)) {
                return null;
            }

            return $error('Acceso denegado', 'No tiene permisos para realizar esta acción.', 403);
        });

        $exceptions->render(function (JsonException $e, Request $request) use ($esApi, $error) {
            if (! $esApi($request)) {
                return null;
            }

            return $error('Solicitud inválida', 'El cuerpo JSON enviado no es válido.', 400);
        });

        $exceptions->render(function (Throwable $e, Request $request) use ($esApi, $error) {
            if (! $esApi($request)) {
                return null;
            }

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

                $tipo = match (true) {
                    $estado === 403 => 'Acceso denegado',
                    $estado >= 500  => 'Error interno',
                    default         => 'Solicitud inválida',
                };

                return $error(
                    $tipo,
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
