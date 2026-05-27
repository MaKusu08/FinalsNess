#!/bin/sh
set -e

# Create .env if not exists
if [ ! -f /app/.env ]; then
    echo "APP_ENV=prod" > /app/.env
    echo "APP_DEBUG=0" >> /app/.env
    echo "APP_SECRET=${APP_SECRET:-$(openssl rand -hex 32)}" >> /app/.env
fi

# Run migrations if DATABASE_URL is set
if [ -n "$DATABASE_URL" ]; then
    echo "Running migrations..."
    php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration || true
fi

# Clear and warmup cache
php bin/console cache:clear --env=prod --no-debug
php bin/console cache:warmup --env=prod --no-debug

# Start FrankenPHP
exec frankenphp run --config /etc/caddy/Caddyfile