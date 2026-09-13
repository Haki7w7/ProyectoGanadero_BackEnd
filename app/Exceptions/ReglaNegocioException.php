<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReglaNegocioException extends Exception
{
    /**
     * Código de estado HTTP a devolver (por defecto 422 Unprocessable Content).
     */
    protected int $codigoHttp;

    public function __construct(string $mensaje, int $codigoHttp = 422)
    {
        parent::__construct($mensaje);

        $this->codigoHttp = $codigoHttp;
    }

    /**
     * Laravel invoca este método automáticamente al capturar la excepción,
     * sin necesidad de registrarla manualmente en el Handler.
     */
    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'error'   => 'Regla de Negocio',
            'mensaje' => $this->getMessage(),
        ], $this->codigoHttp);
    }
}
