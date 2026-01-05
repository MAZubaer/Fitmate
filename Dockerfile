# Dockerfile for Laravel + Vue + Inertia + SQLite (with your versions)

# 1. Build frontend assets
FROM node:24.4.1 AS node-build
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY resources/ resources/
COPY vite.config.js ./
COPY jsconfig.json ./
COPY public/ public/
RUN npm run build

# 2. Build PHP/Laravel backend
FROM php:8.2.12-fpm-alpine

# Install system dependencies
RUN apk add --no-cache bash sqlite sqlite-dev libpng libpng-dev libjpeg-turbo-dev libwebp-dev libxpm-dev freetype-dev oniguruma-dev libzip-dev zip unzip git curl

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_sqlite mbstring zip exif pcntl bcmath gd

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Copy backend files
COPY . .

# Copy built frontend assets
COPY --from=node-build /app/public/build ./public/build

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Set permissions
RUN chown -R www-data:www-data /app/storage /app/bootstrap/cache

# Expose port
EXPOSE 8000

# Entrypoint script to run migrations and start server
CMD php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=8000
