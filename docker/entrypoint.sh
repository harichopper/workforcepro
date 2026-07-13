#!/bin/sh
# ============================================================
# WorkForce Pro – Docker Entrypoint
# Waits for the database to be ready before starting Apache.
# ============================================================
set -e

DB_HOST="${DB_HOST:-127.0.0.1}"
DB_PORT="${DB_PORT:-3306}"

echo "[entrypoint] Waiting for MySQL at ${DB_HOST}:${DB_PORT}..."

MAX_TRIES=30
i=0
while ! nc -z "$DB_HOST" "$DB_PORT" 2>/dev/null; do
    i=$((i+1))
    if [ "$i" -ge "$MAX_TRIES" ]; then
        echo "[entrypoint] ERROR: Database not reachable after ${MAX_TRIES}s. Exiting."
        exit 1
    fi
    echo "[entrypoint] Attempt ${i}/${MAX_TRIES} – retrying in 2s..."
    sleep 2
done

echo "[entrypoint] Database is up. Starting Apache..."
exec "$@"
