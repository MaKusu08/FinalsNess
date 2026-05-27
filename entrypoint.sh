#!/bin/sh
set -e

echo "🚀 Starting Symfony deployment on Railway..."

# Wait for database if DATABASE_URL is set and not using SQLite
if [ -n "$DATABASE_URL" ] && [ "${DATABASE_URL:0:7}" != "sqlite:" ]; then
    echo "⏳ Waiting for database to be ready..."
    
    # Extract host and port from DATABASE_URL
    DB_HOST=$(echo $DATABASE_URL | sed -n 's/.*@\([^:]*\):.*/\1/p')
    DB_PORT=$(echo $DATABASE_URL | sed -n 's/.*:\([0-9]*\)\/.*/\1/p')
    
    # Default values if extraction failed
    DB_HOST=${DB_HOST:-"127.0.0.1"}
    DB_PORT=${DB_PORT:-"5432"}
    
    # Wait for database connection
    until nc -z "$DB_HOST" "$DB_PORT"; do
        echo "Waiting for database at $DB_HOST:$DB_PORT..."
        sleep 2
    done
    echo "✅ Database is ready!"
fi

# Run database migrations if in production
if [ "$APP_ENV" = "prod" ] || [ "$APP_ENV" = "production" ]; then
    echo "🔄 Running database migrations..."
    if php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration; then
        echo "✅ Migrations completed successfully!"
    else
        echo "⚠️  Migration failed, continuing anyway..."
    fi
fi

# Clear and warmup cache for production
if [ "$APP_ENV" = "prod" ] || [ "$APP_ENV" = "production" ]; then
    echo "🔥 Warming up Symfony cache..."
    php bin/console cache:clear --env=prod --no-debug
    php bin/console cache:warmup --env=prod --no-debug
fi

# Set proper permissions
echo "🔧 Setting permissions..."
chown -R www-data:www-data /var/www/symfony/var /var/www/symfony/public
chmod -R 775 /var/www/symfony/var

echo "✅ Entrypoint setup complete!"
echo "🌟 Starting services..."

# Execute the main command (supervisord)
exec "$@"