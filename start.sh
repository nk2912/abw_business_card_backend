#!/usr/bin/env bash
set -e

PORT="${PORT:-80}"

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

echo "==> Configuring Apache to listen on port ${PORT}..."
sed -ri "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
sed -ri "s/<VirtualHost \\*:80>/<VirtualHost *:${PORT}>/g" /etc/apache2/sites-available/*.conf

echo "==> Normalizing Apache MPM modules..."
rm -f /etc/apache2/mods-enabled/mpm_event.conf /etc/apache2/mods-enabled/mpm_event.load
rm -f /etc/apache2/mods-enabled/mpm_worker.conf /etc/apache2/mods-enabled/mpm_worker.load
a2enmod mpm_prefork >/dev/null 2>&1 || true

echo "==> Active Apache MPM modules:"
ls -1 /etc/apache2/mods-enabled/mpm_*.load /etc/apache2/mods-enabled/mpm_*.conf 2>/dev/null || true

echo "==> Apache will listen on port ${PORT}"

echo "==> Validating Apache config..."
apache2ctl configtest

echo "==> Starting Apache..."
exec apache2-foreground
