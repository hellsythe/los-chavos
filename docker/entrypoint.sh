#!/bin/sh
# Preparacion del contenedor en tiempo de arranque.
#
# Se ejecuta como root antes de levantar NGINX Unit. Coolify inyecta las
# variables de entorno al iniciar el contenedor (no en el build), por eso aqui
# es donde se cachea la configuracion de Laravel.

set -e

# Los ficheros que crea root (artisan, supervisor) deben quedar escribibles por
# el usuario "unit" que corre la app y el worker de colas. Con umask 002 los
# ficheros nuevos se crean 664 y con el bit setgid los directorios heredan el
# grupo "unit", asi el worker nunca se queda sin poder escribir en los logs.
umask 002

cd /app

mkdir -p storage/framework/cache/data \
         storage/framework/sessions \
         storage/framework/views \
         storage/logs \
         bootstrap/cache

# Los volumenes persistentes se montan propiedad de root, hay que devolver los
# permisos al usuario "unit" que es el que ejecuta PHP (la app y el worker de
# colas). Si no, el driver de sesion "file" y los logs no se pueden escribir.
#
# - chown/chmod: el grupo "unit" puede leer y escribir todo storage.
# - setgid: los ficheros nuevos heredan el grupo "unit".
# - ACL por defecto (setfacl): los ficheros nuevos se crean 664 aunque los cree
#   un proceso root con umask 022 (por ejemplo "docker exec php artisan ...").
#   Asi el worker nunca se queda sin poder escribir en los logs.
fix_permissions() {
    chown -R unit:unit storage bootstrap/cache 2>/dev/null || true
    chmod -R u+rwX,g+rwX storage bootstrap/cache 2>/dev/null || true
    chmod 2775 storage storage/logs storage/framework storage/framework/cache \
        storage/framework/sessions storage/framework/views bootstrap/cache 2>/dev/null || true
    setfacl -R -m g::rwX storage bootstrap/cache 2>/dev/null || true
    find storage bootstrap/cache -type d -exec setfacl -d -m g::rwX {} + 2>/dev/null || true
}

fix_permissions

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

fix_permissions

exec /usr/local/bin/docker-entrypoint.sh "$@"
