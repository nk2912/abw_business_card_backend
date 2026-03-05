#!/usr/bin/env bash
set -e

echo "==> Running migrations..."
php artisan migrate --force

echo "==> Clearing caches..."
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true

echo "==> Starting Apache..."
exec apache2-foreground