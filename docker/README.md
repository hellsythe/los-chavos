# Contenedor de producción (Docker / Coolify)

La imagen `Dockerfile` (base `unit:1.34.1-php8.3`) corre tres procesos, todos
administrados por **supervisor** (`docker/supervisord.conf`):

| Proceso           | Que hace                                                        |
|-------------------|-----------------------------------------------------------------|
| `unitd`           | Servidor web (NGINX Unit) + PHP-FPM                            |
| `laravel-queue`   | `php artisan queue:work` — procesa los jobs de la cola          |
| `laravel-schedule`| `php artisan schedule:work` — comandos programados de `Kernel.php` |

Antes de arrancar, `docker/entrypoint.sh` cachea la configuración, compila las
vistas, enlaza `storage` y aplica las migraciones.

## Variables de entorno

| Variable           | Default | Descripción                                       |
|--------------------|---------|---------------------------------------------------|
| `RUN_QUEUE_WORKER` | `true`  | Arranca (`true`) o no (`false`) el worker de colas |
| `RUN_SCHEDULER`    | `true`  | Arranca (`true`) o no (`false`) el scheduler        |
| `RUN_MIGRATIONS`   | `true`  | Ejecuta `php artisan migrate` al arrancar          |

Se pueden cambiar en Coolify (Environment Variables) y reconstruir la imagen no
es necesario: se leen al arrancar el contenedor.

## Ver logs y estado

```bash
# Logs de todo el contenedor (incluye el worker)
docker logs -f los-chavos

# Estado de los procesos
docker exec los-chavos supervisorctl -c /etc/supervisor/supervisord.conf status

# Reiniciar solo el worker (por ejemplo tras un deploy)
docker exec los-chavos supervisorctl -c /etc/supervisor/supervisord.conf restart laravel-queue
```

Los logs de Laravel van a `storage/logs/` y los del chatbot a
`storage/logs/bot.log`.

## Almacenamiento persistente (Coolify)

La app vive en `/app` (igual que en el despliegue con Nixpacks), asi que el
volumen persistente se monta en:

```
/data/coolify/applications/<app>/storage-public  ->  /app/storage/app/public
```

Ahí van los archivos subidos por los usuarios (fotos de uniformes, PDF de
tickets, disenos). `php artisan storage:link` crea el enlace
`/app/public/storage` -> `/app/storage/app/public`, por lo que los archivos se
sirven en `https://<dominio>/storage/...`.

Si el mount apunta a otra ruta (por ejemplo `/var/www/html/storage/app/public`)
queda en un directorio que la app no usa y los archivos se pierden en cada
deploy.

## Notas

- El worker corre como el usuario `unit` (el mismo que la app) y usa la
  conexión de base de datos del `config:cache` que genera el entrypoint.
- Parámetros del worker: `--queue=default --sleep=3 --tries=3 --timeout=120
  --max-time=3600` (los mismos que el worker de Plesk). `--max-time` hace que
  el proceso se reinicie cada hora para no acumular memoria.
- Los ficheros de `storage/` quedan con ACL para que el worker pueda escribir
  aunque los cree un proceso root (por ejemplo `docker exec php artisan ...`).
- El scheduler es lo que hace que corran los comandos de `app/Console/Kernel.php`
  (por ejemplo `app:load-statistics-embrodeiry`, diario a las 23:00).

## Otros despliegues

- **Nixpacks** (`nixpacks.toml`): ya tenía su propio worker de colas con
  supervisor.
- **Plesk**: ver `stubs/plesk-supervisor-worker.md` y
  `stubs/plesk-supervisor-worker.conf`.
