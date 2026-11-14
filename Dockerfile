# Shared PHP runtime used for Composer and the final Render container.
FROM php:8.2-cli-alpine AS php-base

RUN apk add --no-cache \
        freetype \
        icu-libs \
        libjpeg-turbo \
        libpng \
        libwebp \
        libxml2 \
        libzip \
        oniguruma \
        postgresql-libs \
    && apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        freetype-dev \
        icu-dev \
        libjpeg-turbo-dev \
        libpng-dev \
        libwebp-dev \
        libxml2-dev \
        libzip-dev \
        oniguruma-dev \
        postgresql-dev \
    && docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
        --with-webp \
    && docker-php-ext-install -j"$(getconf _NPROCESSORS_ONLN)" \
        bcmath \
        dom \
        exif \
        gd \
        mbstring \
        opcache \
        pcntl \
        pdo_pgsql \
        zip \
    && apk del .build-deps

WORKDIR /app


# Install production PHP dependencies with the same PHP extensions as runtime.
FROM php-base AS vendor

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY . .
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts


# Build frontend assets after Composer makes Ziggy available.
FROM node:22-alpine AS frontend

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY . .
COPY --from=vendor /app/vendor ./vendor
RUN npm run build


# Final runtime image used by Render.
FROM php-base AS runtime

COPY --from=vendor /app /app
COPY --from=frontend /app/public/build /app/public/build

RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && rm -f bootstrap/cache/*.php \
    && php artisan package:discover --ansi \
    && chown -R www-data:www-data storage bootstrap/cache

# OPcache keeps compiled PHP in memory instead of recompiling every file on every request.
# The code never changes inside a running container, so timestamp checks are skipped.
RUN { \
        echo 'opcache.enable=1'; \
        echo 'opcache.memory_consumption=128'; \
        echo 'opcache.max_accelerated_files=20000'; \
        echo 'opcache.validate_timestamps=0'; \
    } > /usr/local/etc/php/conf.d/opcache-recommended.ini

# Security hardening (PHP2Shell, October 2026):
# - Refuse to build on a PHP older than the release that fixes CVE-2026-109618/109619,
#   e.g. if a stale cached base image is used.
# - CVE-2026-115560 has no patched release yet; PHP's interim mitigation is to turn off
#   session upload progress in php.ini. Laravel doesn't use PHP's native sessions, so
#   nothing in FitMate depends on it. Remove once a fixed PHP 8.2 release ships.
RUN php -r 'exit(version_compare(PHP_VERSION, "8.2.35", ">=") ? 0 : 1);' \
        || (echo "PHP $(php -r 'echo PHP_VERSION;') is missing the PHP2Shell fixes; rebuild without cache" && exit 1) \
    && { \
        echo 'session.upload_progress.enabled=0'; \
        echo 'expose_php=Off'; \
    } > /usr/local/etc/php/conf.d/security.ini

ENV APP_ENV=production
ENV APP_DEBUG=false
# Fork several PHP server workers so one slow request (e.g. a 10s AI reply)
# no longer blocks every other request. Requires --no-reload below.
ENV PHP_CLI_SERVER_WORKERS=4

EXPOSE 8000

# Render supplies PORT and the environment at runtime, so config/route/view caches are
# built on startup (not at build time). Migrations also run here, because Render's
# Pre-Deploy Command only exists on paid plans; on the free plan new migrations would
# otherwise never run. Already-applied migrations are skipped, so this is a no-op when
# nothing is new. (The cron service overrides CMD, so only the web service migrates.)
CMD ["sh", "-c", "php artisan config:cache && php artisan route:cache && php artisan view:cache && php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=${PORT:-8000} --no-reload"]
