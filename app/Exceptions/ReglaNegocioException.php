<?php

namespace App\Exceptions;

use Exception;

/**
 * Excepción de dominio: se lanza desde los servicios cuando una operación
 * viola una regla de negocio (RN-01 ... RN-07) o cuando un recurso no existe.
 *
 * Ya NO construye la respuesta HTTP por sí misma: el mapeo a JSON se hace de
 * forma centralizada en bootstrap/app.php (->withExceptions(...)), de modo que
 * todas las excepciones de la API comparten el mismo formato.
 *
 * Política de códigos HTTP (Lab 5):
 *  - Violación de regla de negocio  -> 409 Conflict
 *  - Recurso inexistente (404)      -> 404 Not Found
 * Cualquier otro código recibido en el constructor (p. ej. el 422 usado en el
 * Lab 4) se normaliza a 409 al momento de responder.
 */
class ReglaNegocioException extends Exception
{
    public const HTTP_CONFLICT = 409;
    public const HTTP_NOT_FOUND = 404;

    /**
     * Código solicitado por quien lanza la excepción (por defecto 409).
     */
    protected int $codigoHttp;

    public function __construct(string $mensaje, int $codigoHttp = self::HTTP_CONFLICT)
    {
        parent::__construct($mensaje);

        $this->codigoHttp = $codigoHttp;
    }

    /**
     * Código HTTP definitivo con el que se debe responder al cliente:
     * 404 si el servicio indicó "no encontrado"; 409 en cualquier otro caso.
     */
    public function estadoHttp(): int
    {
        return $this->codigoHttp === self::HTTP_NOT_FOUND
            ? self::HTTP_NOT_FOUND
            : self::HTTP_CONFLICT;
    }
}
