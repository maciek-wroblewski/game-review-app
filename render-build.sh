#!/usr/bin/env bash
# exit on error
set -o errexit

echo "Installing composer dependencies..."
composer install --no-dev --optimize-autoloader

echo "Compiling frontend assets..."
npm install
npm run build

echo "Caching config..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Running migrations..."
php artisan migrate --force
