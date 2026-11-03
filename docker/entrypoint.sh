#!/bin/sh
set -e

cd /var/www/html

if [ -z "$APP_KEY" ]; then
    echo "ERROR: falta APP_KEY en el .env. Genérala con:"
    echo "  docker compose run --rm app php artisan key:generate --show"
    exit 1
fi

# Los volúmenes llegan vacíos la primera vez: se crean las carpetas que Laravel necesita.
mkdir -p storage/app/public storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Espera a MySQL (además del healthcheck de docker compose).
intentos=0
until php -r 'try { new PDO("mysql:host=".getenv("DB_HOST").";port=".(getenv("DB_PORT") ?: 3306), getenv("DB_USERNAME"), getenv("DB_PASSWORD")); } catch (Throwable $e) { exit(1); }'; do
    intentos=$((intentos + 1))
    if [ "$intentos" -ge 30 ]; then
        echo "ERROR: no se pudo conectar a MySQL en ${DB_HOST}:${DB_PORT:-3306}"
        exit 1
    fi
    echo "Esperando a MySQL… ($intentos)"
    sleep 2
done

if [ "${GYM_DATOS_DEMO:-false}" = "true" ]; then
    su -s /bin/sh www-data -c "php artisan gym:preparar --demo"
else
    su -s /bin/sh www-data -c "php artisan gym:preparar"
fi

exec "$@"
