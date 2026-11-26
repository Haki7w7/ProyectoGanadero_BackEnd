<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use RuntimeException;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        if (app()->environment('production') && ! app()->runningInConsole()) {
            $missing = [];

            if (empty(config('app.key'))) {
                $missing[] = 'APP_KEY';
            }

            $defaultConnection = config('database.default');
            $dbHost = config("database.connections.{$defaultConnection}.host");
            if (empty($dbHost) || (! app()->configurationIsCached() && empty(env('DB_HOST')))) {
                $missing[] = 'DB_HOST';
            }

            $allowedOrigins = config('cors.allowed_origins', []);
            if (empty($allowedOrigins)) {
                $missing[] = 'CORS_ALLOWED_ORIGINS';
            } elseif (in_array('*', $allowedOrigins, true)) {
                throw new RuntimeException(
                    'Configuración insegura en producción: CORS_ALLOWED_ORIGINS no puede contener el comodín "*".'
                );
            }

            if ($missing !== []) {
                throw new RuntimeException(
                    'Configuración requerida ausente en producción: '.implode(', ', $missing).'.'
                );
            }
        }
    }
}
