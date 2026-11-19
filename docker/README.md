# Contenedor de producción (Docker / Coolify)

La imagen `Dockerfile` (base `unit:1.34.1-php8.3`) corre estos procesos, todos
administrados por **supervisor** (`docker/supervisord.conf`):

| Proceso         | Que hace                                              |
|-----------------|-------------------------------------------------------|
| `unitd`         | Servidor web (NGINX Unit) + PHP-FPM                  |
| `laravel-queue` | `php artisan queue:work` — procesa los jobs de la cola |

El **scheduler** (`php artisan schedule:work`) viene comentado a propósito:
arranca un segundo proceso PHP con la app completa en memoria (~30 MB) para
ejecutar una sola tarea diaria. Se dispara mejor con un cron (ver abajo).

Antes de arrancar, `docker/entrypoint.sh` cachea la configuración, compila las
vistas, enlaza `storage` y aplica las migraciones.

## Variables de entorno

| Variable           | Default | Descripción                                       |
|--------------------|---------|---------------------------------------------------|
| `RUN_QUEUE_WORKER` | `true`  | Arranca (`true`) o no (`false`) el worker de colas |
| `RUN_MIGRATIONS`   | `true`  | Ejecuta `php artisan migrate` al arrancar          |

Se pueden cambiar en Coolify (Environment Variables) y reconstruir la imagen no
es necesario: se leen al arrancar el contenedor.

## Tareas programadas (scheduler)

En `app/Console/Kernel.php` hay una tarea diaria
(`app:load-statistics-embrodeiry` a las 23:00). Para que corra, crea en Coolify
un **cron** para el recurso con esta configuración:

- Comando: `php artisan schedule:run`
- Working directory: `/app`
- Frecuencia: cada minuto (`* * * * *`)

Laravel evalúa las tareas programadas en cada ejecución y solo lanza las que
tocan. Es la alternativa recomendada a dejar `schedule:work` corriendo (ahorra
~30 MB de RAM en el contenedor).

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
- El worker reinicia solo el proceso cada hora (`--max-time=3600`); si se
  cuelga o muere, supervisor lo vuelve a levantar.

## Otros despliegues

- **Nixpacks** (`nixpacks.toml`): ya tenía su propio worker de colas con
  supervisor.
- **Plesk**: ver `stubs/plesk-supervisor-worker.md` y
  `stubs/plesk-supervisor-worker.conf`.
