#!/bin/sh
# Preparacion del contenedor en tiempo de arranque.
#
# Se ejecuta como root antes de levantar NGINX Unit. Coolify inyecta las
# variables de entorno al iniciar el contenedor (no en el build), por eso aqui
# es donde se cachea la configuracion de Laravel.

set -e

cd /var/www/html

mkdir -p storage/framework/cache/data \
         storage/framework/sessions \
         storage/framework/views \
         storage/logs \
         bootstrap/cache

# Los volumenes persistentes se montan propiedad de root, hay que devolver los
# permisos al usuario "unit" que es el que ejecuta PHP. Si no, el driver de
# sesion "file" y los logs no se pueden escribir.
chown -R unit:unit storage bootstrap/cache 2>/dev/null || true
chmod -R u+rwX,g+rwX storage bootstrap/cache 2>/dev/null || true

echo "[laravel] cacheando configuracion..."
php artisan config:cache --no-interaction || echo "[laravel] config:cache fallo, se continua sin cache"

echo "[laravel] compilando vistas..."
php artisan view:cache --no-interaction || echo "[laravel] view:cache fallo, se continua sin cache"

echo "[laravel] creando enlace de storage..."
php artisan storage:link --force --no-interaction || echo "[laravel] storage:link fallo"

# El driver de sesion "database" necesita la tabla "sessions". La base de datos
# puede tardar unos segundos en estar lista, por eso se reintenta.
if [ "$RUN_MIGRATIONS" != "false" ]; then
    attempt=1
    until php artisan migrate --force --no-interaction; do
        if [ "$attempt" -ge 5 ]; then
            echo "[laravel] migrate fallo, revisa la conexion con la base de datos"
            break
        fi
        attempt=$((attempt + 1))
        echo "[laravel] la base de datos aun no responde, reintento $attempt..."
        sleep 5
    done
fi

chown -R unit:unit storage bootstrap/cache 2>/dev/null || true

exec /usr/local/bin/docker-entrypoint.sh "$@"
