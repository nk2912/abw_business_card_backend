#!/usr/bin/env bash
set -e

echo "==> Preparing Laravel cache directories..."
mkdir -p \
  storage/framework/views \
  storage/framework/cache \
  storage/framework/sessions \
  bootstrap/cache

chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

echo "==> Running migrations..."
php artisan migrate --force

echo "==> Starting Apache..."
exec apache2-foreground
