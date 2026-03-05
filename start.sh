#!/usr/bin/env bash
set -e

echo "==> Checking DB connection & running migrations..."
php artisan migrate --force

echo "==> Caching config/routes..."
php artisan config:cache || true
php artisan route:cache || true

echo "==> Starting Apache..."
exec apache2-foreground