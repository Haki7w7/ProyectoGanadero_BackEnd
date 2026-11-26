<?php

namespace App\Exceptions;

use Exception;

class ReglaNegocioException extends Exception
{
    public const HTTP_CONFLICT = 409;
    public const HTTP_NOT_FOUND = 404;

    protected int $codigoHttp;

    public function __construct(string $mensaje, int $codigoHttp = self::HTTP_CONFLICT)
    {
        parent::__construct($mensaje);

        $this->codigoHttp = $codigoHttp;
    }

    public function estadoHttp(): int
    {
        return $this->codigoHttp === self::HTTP_NOT_FOUND
            ? self::HTTP_NOT_FOUND
            : self::HTTP_CONFLICT;
    }
}
