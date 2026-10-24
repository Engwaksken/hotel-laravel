#!/usr/bin/env bash
set -euo pipefail
cd "${HMS_APP_PATH:-/var/www/hms}"
php artisan down --retry=30 || true
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan hotel:health-check --store
php artisan queue:restart
php artisan up
