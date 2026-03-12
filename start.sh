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
rm -f /etc/apache2/mods-enabled/mpm_event.conf /etc/apache2/mods-enabled/mpm_event.load
rm -f /etc/apache2/mods-enabled/mpm_worker.conf /etc/apache2/mods-enabled/mpm_worker.load
a2enmod mpm_prefork >/dev/null 2>&1 || true

echo "==> Active Apache MPM modules:"
ls -1 /etc/apache2/mods-enabled/mpm_*.load /etc/apache2/mods-enabled/mpm_*.conf 2>/dev/null || true

echo "==> Validating Apache config..."
apache2ctl configtest

echo "==> Starting Apache..."
exec apache2-foreground
