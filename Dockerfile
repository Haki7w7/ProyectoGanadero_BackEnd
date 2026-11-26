# Etapa 1: Construcción de dependencias PHP (Composer sin dev)
FROM serversideup/php:8.4-fpm-nginx AS vendor

WORKDIR /var/www/html

COPY --chown=www-data:www-data composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --no-scripts \
    --no-autoloader \
    --no-interaction \
    --prefer-dist

COPY --chown=www-data:www-data . .

RUN composer dump-autoload --optimize --no-dev

# Etapa 2: Imagen final mínima de producción
FROM serversideup/php:8.4-fpm-nginx

ENV APP_ENV=production \
    APP_DEBUG=false \
    PHP_OPCACHE_ENABLE=1 \
    AUTORUN_ENABLED=true

WORKDIR /var/www/html

COPY --from=vendor --chown=www-data:www-data /var/www/html /var/www/html

USER root

RUN printf '%s\n' \
    '#!/bin/sh' \
    'if [ "${APP_ENV:-production}" = "production" ]; then' \
    '    missing=""' \
    '    if [ -z "${APP_KEY:-}" ]; then missing="${missing} APP_KEY"; fi' \
    '    if [ -z "${DB_HOST:-}" ]; then missing="${missing} DB_HOST"; fi' \
    '    if [ -z "${DB_DATABASE:-}" ]; then missing="${missing} DB_DATABASE"; fi' \
    '    if [ -z "${DB_USERNAME:-}" ]; then missing="${missing} DB_USERNAME"; fi' \
    '    if [ -z "${CORS_ALLOWED_ORIGINS:-}" ]; then missing="${missing} CORS_ALLOWED_ORIGINS"; fi' \
    '    if [ -n "$missing" ]; then' \
    '        echo "ERROR [Fail-Fast]: Variables de entorno obligatorias ausentes:${missing}" >&2' \
    '        exit 1' \
    '    fi' \
    '    cors_clean=$(printf "%s" "$CORS_ALLOWED_ORIGINS" | tr -d " ")' \
    '    case ",${cors_clean}," in' \
    '        *,"*",*)' \
    '            echo "ERROR [Fail-Fast]: CORS_ALLOWED_ORIGINS no puede contener el comodin * en produccion." >&2' \
    '            exit 1' \
    '            ;;' \
    '    esac' \
    'fi' \
    > /etc/entrypoint.d/00-validate-env.sh \
    && chmod 0755 /etc/entrypoint.d/00-validate-env.sh

USER www-data

RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && php artisan package:discover --ansi

EXPOSE 8080
