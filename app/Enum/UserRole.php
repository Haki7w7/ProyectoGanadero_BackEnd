<?php

namespace App\Enum;

enum UserRole: string
{
    
    case ADMIN       = 'admin';
    case OPERARIO    = 'operario';
    case VETERINARIO = 'veterinario';

}
