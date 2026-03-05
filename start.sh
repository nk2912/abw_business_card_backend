#!/usr/bin/env bash
set -e

echo "==> Running Laravel optimizations..."
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true

echo "==> Running migrations..."
php artisan migrate --force || true

echo "==> Starting Apache..."
exec apache2-foreground