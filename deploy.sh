#!/usr/bin/env bash
###############################################################################
# Production deployment script for the Restaurant POS.
#
# Usage:  ./deploy.sh
#
# It builds the Docker images, runs database migrations and (re)starts the
# full stack defined in docker-compose.prod.yml with zero manual steps.
###############################################################################
set -euo pipefail

COMPOSE_FILE="docker-compose.prod.yml"
COMPOSE="docker compose -f ${COMPOSE_FILE}"

log() { printf '\n\033[1;34m==> %s\033[0m\n' "$1"; }
fail() { printf '\n\033[1;31mERROR: %s\033[0m\n' "$1" >&2; exit 1; }

# ---------------------------------------------------------------------------
# Pre-flight checks
# ---------------------------------------------------------------------------
command -v docker >/dev/null 2>&1 || fail "Docker is not installed or not on PATH."
docker compose version >/dev/null 2>&1 || fail "Docker Compose v2 is required."
[ -f "$COMPOSE_FILE" ] || fail "${COMPOSE_FILE} not found. Run from the project root."

if [ ! -f .env ]; then
    log "No .env found — creating one from .env.production.example"
    cp .env.production.example .env
    fail "A fresh .env was created. Fill in the secrets (APP_KEY, DB_PASSWORD, REVERB_*) then re-run ./deploy.sh"
fi

# ---------------------------------------------------------------------------
# Optionally pull the latest code (skipped when not a git checkout)
# ---------------------------------------------------------------------------
if [ -d .git ] && [ "${SKIP_GIT_PULL:-0}" != "1" ]; then
    log "Pulling latest code"
    git pull --ff-only || fail "git pull failed — resolve conflicts and retry."
fi

# ---------------------------------------------------------------------------
# Build images
# ---------------------------------------------------------------------------
log "Building Docker images"
$COMPOSE build

# ---------------------------------------------------------------------------
# Ensure an application key exists (generated once, persisted to .env)
# ---------------------------------------------------------------------------
if ! grep -qE '^APP_KEY=base64:' .env; then
    log "Generating application key"
    $COMPOSE run --rm --no-deps app php artisan key:generate --force
fi

# ---------------------------------------------------------------------------
# Bring up datastores first so migrations have somewhere to run
# ---------------------------------------------------------------------------
log "Starting database and cache services"
$COMPOSE up -d db redis

# ---------------------------------------------------------------------------
# Start the stack. The app container's entrypoint runs migrations (--force)
# and rebuilds the framework caches automatically.
# ---------------------------------------------------------------------------
log "Starting application stack"
$COMPOSE up -d --remove-orphans

# ---------------------------------------------------------------------------
# Post-deploy housekeeping
# ---------------------------------------------------------------------------
log "Pruning dangling images"
docker image prune -f >/dev/null 2>&1 || true

log "Deployment complete. Current status:"
$COMPOSE ps
