<?php

namespace App\Enum;

enum UserRole: string
{
    
    case ADMIN       = 'admin';
    case OPERARIO    = 'operario';
    case VETERINARIO = 'veterinario';

    /** Capacidades del token según rol. */
    public function abilities(): array
    {
        return match ($this) {
            self::ADMIN       => ['*'],
            self::VETERINARIO => ['animales:read', 'tratamientos:manage', 'pesajes:create'],
            self::OPERARIO    => ['animales:read', 'pesajes:create', 'potreros:read'],
        };
    }

}
