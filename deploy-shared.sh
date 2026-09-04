#!/usr/bin/env bash
###############################################################################
# Shared-hosting deployment (cPanel, no Docker, no root).
#
# Run on the server via SSH (manual or GitHub Actions):
#   PHP_BIN=/usr/local/bin/ea-php83 ./deploy-shared.sh
#
# What it does: pull code -> composer install -> migrate -> refresh caches.
# Node is NOT needed: public/build is committed to git.
###############################################################################
set -euo pipefail

PHP_BIN="${PHP_BIN:-php}"

log() { printf '\n\033[1;34m==> %s\033[0m\n' "$1"; }
fail() { printf '\n\033[1;31mERROR: %s\033[0m\n' "$1" >&2; exit 1; }

command -v git >/dev/null 2>&1 || fail "git is not available on this account."
[ -f artisan ] || fail "Run from the project root (artisan not found)."
[ -f .env ] || fail ".env missing. Create it from .env.production.example first."

if [ "${SKIP_GIT_PULL:-0}" != "1" ]; then
    log "Pulling latest code"
    git fetch origin main
    git reset --hard origin/main
fi

log "PHP version: $("${PHP_BIN}" -r 'echo PHP_VERSION;')"
"${PHP_BIN}" -r 'version_compare(PHP_VERSION, "8.2.0", ">=") or exit(1);' \
    || fail "PHP 8.2+ required. Set PHP_BIN, e.g. PHP_BIN=/usr/local/bin/ea-php83"

if command -v composer >/dev/null 2>&1; then
    COMPOSER="${PHP_BIN} $(command -v composer)"
elif [ -f composer.phar ]; then
    COMPOSER="${PHP_BIN} composer.phar"
else
    fail "composer not found. Upload composer.phar or ask your host to enable it."
fi

log "Installing PHP dependencies"
# shellcheck disable=SC2086
${COMPOSER} install --no-dev --optimize-autoloader --no-interaction

log "Running migrations"
"${PHP_BIN}" artisan migrate --force

log "Linking storage"
"${PHP_BIN}" artisan storage:link || true

log "Refreshing caches"
"${PHP_BIN}" artisan optimize
"${PHP_BIN}" artisan view:cache
"${PHP_BIN}" artisan event:cache || true

log "Deployment complete"
