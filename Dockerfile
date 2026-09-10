# syntax=docker/dockerfile:1

# Build the Vite assets independently so the production image does not contain
# Node.js or node_modules.
FROM node:22-bookworm-slim AS assets

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY vite.config.js ./
COPY resources ./resources
COPY public ./public

RUN npm run build


FROM php:8.3-apache-bookworm

ENV APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stderr \
    COMPOSER_ALLOW_SUPERUSER=1

WORKDIR /var/www/html

# PHP extensions required by Laravel, Supabase PostgreSQL, image uploads, and
# DomPDF. Apache is used as the HTTP server for Render's Web Service.
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libpq-dev \
        libzip-dev \
        unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" gd pdo_pgsql zip \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY . ./
COPY --from=assets /app/public/build ./public/build
COPY docker/apache-laravel.conf /etc/apache2/conf-available/laravel.conf
COPY docker/render-entrypoint.sh /usr/local/bin/render-entrypoint

# Package discovery is needed for laravel-dompdf. The directories below must
# remain writable because Laravel compiles views and writes its runtime logs.
RUN composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction \
    && mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && php artisan storage:link --force \
    && chown -R www-data:www-data storage bootstrap/cache \
    && a2enconf laravel \
    && chmod +x /usr/local/bin/render-entrypoint

# Render sets PORT=10000 by default. The entrypoint also honours a custom PORT.
EXPOSE 10000

ENTRYPOINT ["render-entrypoint"]
