# syntax=docker/dockerfile:1
FROM node:22-alpine AS frontend
WORKDIR /build
COPY package*.json ./
RUN npm ci
COPY resources ./resources
COPY vite.config.js ./
RUN npm run build

FROM php:8.3-fpm-bookworm AS php-base
RUN apt-get update && apt-get install -y --no-install-recommends libicu-dev libzip-dev libonig-dev libpng-dev libjpeg62-turbo-dev libfreetype6-dev libsqlite3-dev libfcgi-bin unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" pdo_mysql pdo_sqlite mbstring intl zip gd bcmath pcntl opcache \
    && rm -rf /var/lib/apt/lists/*
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
WORKDIR /var/www/html

FROM php-base AS dependencies
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-scripts --no-autoloader
COPY . .
RUN composer dump-autoload --no-dev --classmap-authoritative --no-scripts

FROM php-base AS app
RUN groupadd --gid 1000 aqua && useradd --uid 1000 --gid aqua --create-home aqua
COPY --from=dependencies --chown=aqua:aqua /var/www/html /var/www/html
COPY --from=frontend --chown=aqua:aqua /build/public/build ./public/build
COPY docker/php.ini /usr/local/etc/php/conf.d/aquasecure.ini
COPY docker/fpm.conf /usr/local/etc/php-fpm.d/zz-aquasecure.conf
COPY --chmod=755 docker/entrypoint.sh /usr/local/bin/aquasecure-entrypoint
RUN mkdir -p storage/app/private storage/app/public storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chown -R aqua:aqua storage bootstrap/cache
USER aqua
EXPOSE 9000
ENTRYPOINT ["aquasecure-entrypoint"]
CMD ["php-fpm", "-F"]

FROM nginxinc/nginx-unprivileged:1.31-alpine AS web
COPY docker/nginx.conf /etc/nginx/conf.d/default.conf
COPY --from=dependencies /var/www/html/public /var/www/html/public
COPY --from=frontend /build/public/build /var/www/html/public/build
EXPOSE 8080
