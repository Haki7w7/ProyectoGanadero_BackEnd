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

ENV PHP_OPCACHE_ENABLE=1 \
    AUTORUN_ENABLED=true

WORKDIR /var/www/html

COPY --from=vendor --chown=www-data:www-data /var/www/html /var/www/html

RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && php artisan package:discover --ansi

EXPOSE 8080

USER www-data
