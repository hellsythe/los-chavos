FROM unit:1.34.1-php8.3

RUN apt-get update && apt-get install -y --no-install-recommends \
    curl unzip git libicu-dev libzip-dev libpng-dev libjpeg-dev libfreetype6-dev libssl-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" pcntl opcache pdo_mysql intl zip gd exif ftp bcmath \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && rm -rf /var/lib/apt/lists/*

# Node para compilar los assets de Vite (public/build)
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y --no-install-recommends nodejs \
    && rm -rf /var/lib/apt/lists/*

# output_buffering es obligatorio: sin el, cualquier salida accidental antes
# del response (por ejemplo el salto de linea inicial de
# vendor/sdkconsultoria/core/routes/api.php) provoca "headers already sent" y
# PHP descarta TODOS los headers, incluido Set-Cookie, dejando la app sin
# sesion. En Plesk funciona porque su php.ini ya tiene output_buffering.
RUN { \
        echo "opcache.enable=1"; \
        echo "opcache.jit=tracing"; \
        echo "opcache.jit_buffer_size=256M"; \
        echo "opcache.validate_timestamps=0"; \
        echo "opcache.max_accelerated_files=20000"; \
        echo "memory_limit=512M"; \
        echo "upload_max_filesize=64M"; \
        echo "post_max_size=64M"; \
        echo "expose_php=0"; \
        echo "output_buffering=4096"; \
    } > /usr/local/etc/php/conf.d/custom.ini

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

WORKDIR /var/www/html

# Las dependencias se instalan antes de copiar el codigo para no invalidar la
# capa de cache en cada deploy.
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-scripts --no-autoloader

COPY package.json package-lock.json ./

COPY . .

# Los assets de Vite necesitan vendor/sdkconsultoria/base (dependencia file:),
# por eso npm corre despues de composer install.
RUN composer dump-autoload --optimize --no-dev --no-interaction \
    && npm ci --no-audit --no-fund \
    && npm run build \
    && rm -rf node_modules \
    && mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chown -R unit:unit storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

COPY docker/entrypoint.sh /usr/local/bin/laravel-entrypoint.sh
RUN chmod +x /usr/local/bin/laravel-entrypoint.sh

COPY unit.json /docker-entrypoint.d/unit.json

EXPOSE 8000

ENTRYPOINT ["/usr/local/bin/laravel-entrypoint.sh"]
CMD ["unitd", "--no-daemon"]
