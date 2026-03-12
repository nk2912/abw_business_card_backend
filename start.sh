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

echo "==> Normalizing Apache MPM modules..."
a2dismod mpm_event mpm_worker >/dev/null 2>&1 || true
a2enmod mpm_prefork >/dev/null 2>&1 || true

echo "==> Validating Apache config..."
apache2ctl configtest

echo "==> Starting Apache..."
exec apache2-foreground
