<?php

namespace App\Support;

final class Abilities
{
    public const ROL_ADMIN = 'admin';

    public const ROL_VETERINARIO = 'veterinario';

    public const ROL_OPERARIO = 'operario';

    public static function rolesValidos(): array
    {
        return [self::ROL_ADMIN, self::ROL_VETERINARIO, self::ROL_OPERARIO];
    }

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
