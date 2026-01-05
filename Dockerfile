# Dockerfile for Laravel + Vue + Inertia + SQLite (with your versions)


# 1. Build PHP/Laravel backend and frontend assets
FROM php:8.2.12-fpm-alpine


# Install system dependencies
RUN apk add --no-cache bash sqlite sqlite-dev libpng libpng-dev libjpeg-turbo-dev libwebp-dev libxpm-dev freetype-dev oniguruma-dev libzip-dev zip unzip git curl

# Install Node.js 24.x (official binaries)
ENV NODE_VERSION=24.4.1
RUN curl -fsSLO https://nodejs.org/dist/v$NODE_VERSION/node-v$NODE_VERSION-linux-x64.tar.xz \
	&& tar -xJf node-v$NODE_VERSION-linux-x64.tar.xz -C /usr/local --strip-components=1 \
	&& rm node-v$NODE_VERSION-linux-x64.tar.xz \
	&& ln -s /usr/local/bin/node /usr/bin/node \
	&& ln -s /usr/local/bin/npm /usr/bin/npm \
	&& ln -s /usr/local/bin/npx /usr/bin/npx

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_sqlite mbstring zip exif pcntl bcmath gd

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Copy backend files
COPY . .

# Install PHP dependencies (including Ziggy)
RUN composer install --no-dev --optimize-autoloader

# Install Node dependencies and build frontend
RUN npm install && npm run build

# Set permissions
RUN chown -R www-data:www-data /app/storage /app/bootstrap/cache

# Expose port
EXPOSE 8000

# Entrypoint script to run migrations and start server
CMD php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=8000
