<?php

namespace App\Support;

/**
 * Única fuente de verdad para las capacidades (abilities) de los tokens de
 * Sanctum y para los roles válidos del sistema.
 *
 * Antes estas tablas vivían duplicadas dentro de LoginController y
 * RegisterController, lo que permitía que se desincronizaran entre sí.
 */
final class Abilities
{
    public const ROL_ADMIN = 'admin';

    public const ROL_VETERINARIO = 'veterinario';

    public const ROL_OPERARIO = 'operario';

    /**
     * Roles aceptados por la API.
     *
     * @return list<string>
     */
    public static function rolesValidos(): array
    {
        return [self::ROL_ADMIN, self::ROL_VETERINARIO, self::ROL_OPERARIO];
    }

    /**
     * Capacidades del token según el rol.
     *
     * @return list<string>
     */
    public static function paraRol(string $rol): array
    {
        return match ($rol) {
            self::ROL_ADMIN => ['*'],
            self::ROL_VETERINARIO => ['animales:read', 'tratamientos:manage', 'pesajes:create'],
            self::ROL_OPERARIO => ['animales:read', 'pesajes:create', 'potreros:read'],
            default => [],
        };
    }
}
