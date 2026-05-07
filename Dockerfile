# Build stage
FROM node:22-slim AS frontend

WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . .
RUN npm run build

# Build PHP dependencies
FROM composer:latest AS builder

COPY . /app
WORKDIR /app
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Runtime stage
FROM php:8.3-fpm

WORKDIR /app

# Install build tools and libraries for PHP extensions
RUN apt-get update && apt-get install -y \
    libsqlite3-dev \
    libpq-dev \
    && docker-php-ext-install pdo pdo_sqlite pdo_mysql \
    && apt-get remove -y libsqlite3-dev libpq-dev \
    && apt-get autoremove -y \
    && apt-get install -y --no-install-recommends libpq5 libsqlite3-0 \
    && rm -rf /var/lib/apt/lists/*

# Copy PHP configuration
COPY docker/php.ini /usr/local/etc/php/conf.d/app.ini

# Copy from builder
COPY --from=builder --chown=www-data:www-data /app /app
COPY --from=frontend /app/public/build /app/public/build

# Set permissions
RUN chown -R www-data:www-data /app/storage /app/bootstrap/cache

USER www-data

EXPOSE 9000

CMD ["php-fpm"]
