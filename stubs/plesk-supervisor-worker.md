# Laravel Queue Worker Supervisor - Guía de instalación en Plesk

## 1. Ajustar el archivo de configuración

Edita `stubs/plesk-supervisor-worker.conf`:

- `command`: reemplaza `/var/www/vhosts/los-chavos.com/artisan` por la ruta real de tu proyecto
- `user`: reemplaza `loschavos` por el usuario del sistema de Plesk (suele ser el nombre del dominio o `psaserv`)
- `stdout_logfile`: misma ruta que `command` para los logs

## 2. Subir el archivo al servidor

Por SSH:

```bash
ssh usuario@tu-servidor

# Crear el directorio
sudo mkdir -p /etc/supervisor/conf.d

# Subir el archivo (ajusta la ruta local)
sudo cp plesk-supervisor-worker.conf /etc/supervisor/conf.d/los-chavos-worker.conf
```

## 3. Activar y arrancar

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start los-chavos-worker:*
sudo supervisorctl status los-chavos-worker:*
```

Deberías ver:

```
los-chavos-worker:los-chavos-worker_00   RUNNING   pid 12345, uptime 0:00:05
```

## 4. Verificar

```bash
# Logs en vivo
tail -f /var/www/vhosts/los-chavos.com/storage/logs/worker.log

# Estado de los jobs
cd /var/www/vhosts/los-chavos.com
php artisan queue:monitor
```

## 5. Actualizar configuración del chatbot no se refleja — qué hacer

El worker PHP mantiene las clases y OPcache en memoria entre jobs. Aunque la cache de Laravel (archivo) se invalida al guardar, el proceso del worker puede seguir usando código viejo.

**Diagnóstico rápido:**

```bash
ssh tu-usuario@plesk
cd /var/www/vhosts/los-chavos.site/httpdocs

# 1. Verificar que los datos están guardados en la DB
php artisan tinker --execute='
echo \App\Models\Setting::where("name", "business_hours")->first()?->value . PHP_EOL;
'

# 2. Probar el BusinessInfoService directamente (sin worker)
php artisan tinker --execute='
echo app(\App\Services\BusinessInfoService::class)->getBusinessContext() . PHP_EOL;
'

# 3. Si el paso 2 muestra datos nuevos, el problema es el worker → reinicia
# 4. Si el paso 2 muestra datos viejos, el problema es el guardado → revisa logs
```

**Forzar recarga (3 pasos críticos):**

```bash
# 1. Limpiar todas las caches
php artisan optimize:clear

# 2. Reiniciar el worker (esto recarga OPcache y memoria)
sudo supervisorctl restart los-chavos-worker:*

# 3. Verificar
sudo supervisorctl status los-chavos-worker:*

# 4. Probar mandando un WhatsApp de prueba
```

**Configuración óptima del worker para auto-refresh:**

La config actual usa `--max-time=3600` que reinicia el proceso cada hora. Si quieres que se reinicie más frecuentemente (ej. cada 15 min) para evitar stale caches, edita el conf:

```ini
command=... artisan queue:work ... --max-time=900   # 15 min
```

## 6. Comandos útiles

```bash
# Reiniciar el worker (recomendado tras deploy)
sudo supervisorctl restart los-chavos-worker:*

# Detener
sudo supervisorctl stop los-chavos-worker:*

# Ver logs de errores de supervisor
sudo tail -f /var/log/supervisor/supervisord.log
```

## Notas

- `--max-time=3600`: el worker se reinicia cada hora para evitar memory leaks
- `--sleep=3`: espera 3s entre polls cuando no hay jobs
- `--timeout=120`: mata jobs que tarden más de 2 min
- `--tries=3`: reintenta 3 veces antes de mover a `failed_jobs`
- `autorestart=true`: si crashea, supervisor lo reinicia automáticamente
- `stopsignal=QUIT` + `stopwaitsecs=3600`: permite que el job en ejecución termine antes de matar el worker (importante para `ProcessBotResponse` que llama a OpenAI)
