# syntax=docker/dockerfile:1

## ----------------------------------------------------------------##
## Stage 1: build frontend assets
## ----------------------------------------------------------------##
FROM node:22-alpine AS assets

WORKDIR /app

COPY package.json package-lock.json vite.config.js ./
COPY resources ./resources
COPY public ./public

RUN npm ci && npm run build

## ----------------------------------------------------------------##
## Stage 2: production PHP image
## ----------------------------------------------------------------##
FROM php:8.5-apache

# System packages + PHP extensions used by the app (gd for images,
# pdo_mysql for the database, zip for composer tooling).
RUN apt-get update && apt-get install -y --no-install-recommends \
        libpng-dev \
        libjpeg-dev \
        libfreetype6-dev \
        libzip-dev \
        zip \
        unzip \
        libicu-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd pdo_mysql zip pcntl exif \
    # Optional on PHP 8.5 images: build each separately so one failure
    # never blocks the image.
    && (docker-php-ext-install -j$(nproc) intl || echo "intl skipped") \
    && (docker-php-ext-install -j$(nproc) opcache || echo "opcache skipped") \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Serve Laravel's public/ directory.
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Production PHP settings.
RUN { \
        echo 'opcache.enable=1'; \
        echo 'opcache.memory_consumption=192'; \
        echo 'opcache.max_accelerated_files=20000'; \
        echo 'opcache.validate_timestamps=0'; \
        echo 'memory_limit=256M'; \
        echo 'upload_max_filesize=20M'; \
        echo 'post_max_size=25M'; \
        echo 'realpath_cache_size=4096K'; \
        echo 'realpath_cache_ttl=600'; \
    } > /usr/local/etc/php/conf.d/production.ini

WORKDIR /var/www/html

# Application source (vendor/ is rebuilt fresh inside the image).
COPY . .
RUN composer install --no-dev --no-interaction --no-progress --prefer-dist --optimize-autoloader --no-scripts \
    && rm -rf tests .phpunit.result.cache

# Built frontend assets from stage 1.
COPY --from=assets /app/public/build ./public/build

# Writable runtime directories.
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R ug+rwx storage bootstrap/cache

COPY docker/start.sh /usr/local/bin/start-container
RUN chmod +x /usr/local/bin/start-container

EXPOSE 80

ENTRYPOINT ["start-container"]
