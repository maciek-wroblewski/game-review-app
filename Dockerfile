# Build stage
FROM php:8.3-fpm-alpine AS builder

WORKDIR /app

# Install build dependencies
RUN apk add --no-cache \
    build-base \
    postgresql-dev \
    mysql-client \
    sqlite-dev

# Install PHP extensions
RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    pdo_pgsql \
    pdo_sqlite \
    bcmath

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy composer files and install dependencies
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --prefer-dist

# Copy application
COPY . .

# Generate app key and cache
RUN php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache

# Runtime stage
FROM php:8.3-fpm-alpine

WORKDIR /app

# Install runtime dependencies
RUN apk add --no-cache \
    postgresql-client \
    mysql-client \
    sqlite-libs \
    nginx

# Install PHP extensions
RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    pdo_pgsql \
    pdo_sqlite \
    bcmath

# Create non-root user
RUN addgroup -g 1000 laravel && \
    adduser -D -u 1000 -G laravel laravel

# Copy from builder
COPY --from=builder --chown=laravel:laravel /app /app

# Copy storage and bootstrap cache directories
RUN mkdir -p storage/logs storage/framework/cache storage/framework/sessions storage/framework/views && \
    chown -R laravel:laravel storage bootstrap/cache

# Create SQLite database directory if needed
RUN mkdir -p database && \
    chown -R laravel:laravel database

USER laravel

# Health check
HEALTHCHECK --interval=10s --timeout=3s --start-period=5s --retries=3 \
    CMD php -r "exit((int)!file_exists('/app/storage/logs/laravel.log'));"

EXPOSE 9000

CMD ["php-fpm"]
