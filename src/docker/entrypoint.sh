#!/bin/sh
set -e

echo "🚀 Preparing Symfony Application..."

# CRITICAL FIX: Force the entire script context into production mode
export APP_ENV=prod

# Warm up the production cache safely
echo "🧹 Clearing and warming up Symfony cache for production..."
php bin/console cache:clear --no-debug
php bin/console cache:warmup --no-debug

# Run Database Migrations automatically on deployment
echo "📥 Running database migrations..."
php bin/console doctrine:migrations:migrate --no-interaction

echo "🟢 Starting PHP-FPM..."
php-fpm -D

echo "⚡ Starting Nginx Server..."
exec nginx -g "daemon off;"