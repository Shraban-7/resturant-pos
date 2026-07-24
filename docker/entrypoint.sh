#!/usr/bin/env bash
set -euo pipefail

cd /var/www/html

# Ensure the writable framework directories exist even when storage/ is backed
# by an empty named volume on a fresh host.
mkdir -p \
    storage/app/public \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs

# ---------------------------------------------------------------------------
# Per-container framework caches. Each container has its own image layer, so
# rebuilding the caches here is safe and keeps every role (fpm, worker,
# scheduler, reverb) consistent with the deployed code.
# ---------------------------------------------------------------------------
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# ---------------------------------------------------------------------------
# One-time release tasks. Only the primary "app" role runs migrations and
# creates the storage symlink so concurrent containers never race each other.
# ---------------------------------------------------------------------------
if [ "${CONTAINER_ROLE:-app}" = "app" ]; then
    echo "Waiting for the database to accept connections..."
    ATTEMPTS=0
    until php artisan db:monitor >/dev/null 2>&1 || [ "$ATTEMPTS" -ge 30 ]; do
        ATTEMPTS=$((ATTEMPTS + 1))
        sleep 2
    done

    php artisan migrate --force --no-interaction

    if [ ! -L public/storage ]; then
        php artisan storage:link || true
    fi
fi

exec "$@"
