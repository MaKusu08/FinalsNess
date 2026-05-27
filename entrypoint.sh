#!/bin/sh
set -e

echo "🚀 Starting Symfony deployment on Railway..."

# Create required directories
mkdir -p /var/www/symfony/var/cache
mkdir -p /var/www/symfony/var/log
mkdir -p /var/www/symfony/var/sessions

# Set permissions
chown -R www-data:www-data /var/www/symfony/var
chmod -R 775 /var/www/symfony/var
chmod -R 775 /var/www/symfony/public

# Update nginx port if PORT variable is set
if [ -n "$PORT" ]; then
    echo "Setting nginx to listen on port $PORT"
    # Try different sed patterns
    if grep -q "listen 80;" /etc/nginx/conf.d/default.conf; then
        sed -i "s/listen 80;/listen $PORT;/g" /etc/nginx/conf.d/default.conf
    else
        sed -i "s/listen .*;/listen $PORT;/g" /etc/nginx/conf.d/default.conf
    fi
    echo "Nginx configured to listen on port $PORT"
fi

# Wait for database if DATABASE_URL is set and not using SQLite
if [ -n "$DATABASE_URL" ] && [ "${DATABASE_URL:0:7}" != "sqlite:" ]; then
    echo "⏳ Waiting for database to be ready..."
    
    # Extract host and port from DATABASE_URL
    DB_HOST=$(echo $DATABASE_URL | sed -n 's/.*@\([^:]*\):.*/\1/p')
    DB_PORT=$(echo $DATABASE_URL | sed -n 's/.*:\([0-9]*\)\/.*/\1/p')
    
    # Default values if extraction failed
    if [ -z "$DB_HOST" ]; then
        DB_HOST="127.0.0.1"
    fi
    if [ -z "$DB_PORT" ]; then
        DB_PORT="3306"
    fi
    
    echo "Checking database at $DB_HOST:$DB_PORT"
    
    # Wait for database connection with timeout
    timeout=60
    while ! nc -z "$DB_HOST" "$DB_PORT" 2>/dev/null && [ $timeout -gt 0 ]; do
        echo "Waiting for database at $DB_HOST:$DB_PORT... ($timeout seconds left)"
        sleep 2
        timeout=$((timeout - 2))
    done
    
    if [ $timeout -le 0 ]; then
        echo "⚠️  Database connection timeout, continuing anyway..."
    else
        echo "✅ Database is ready!"
    fi
fi

# Run database migrations if in production
if [ "$APP_ENV" = "prod" ] || [ "$APP_ENV" = "production" ]; then
    echo "🔄 Running database migrations..."
    if php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration --env=prod 2>&1; then
        echo "✅ Migrations completed successfully!"
    else
        echo "⚠️  Migration failed, continuing anyway..."
    fi
fi

# Clear and warmup cache for production
if [ "$APP_ENV" = "prod" ] || [ "$APP_ENV" = "production" ]; then
    echo "🔥 Warming up Symfony cache..."
    php bin/console cache:clear --env=prod --no-debug 2>&1 || true
    php bin/console cache:warmup --env=prod --no-debug 2>&1 || true
fi

# Test PHP-FPM configuration
echo "Testing PHP-FPM configuration..."
if php-fpm -t 2>&1; then
    echo "✅ PHP-FPM configuration is valid"
else
    echo "❌ PHP-FPM configuration has errors"
    exit 1
fi

# Test Nginx configuration
echo "Testing Nginx configuration..."
if nginx -t 2>&1; then
    echo "✅ Nginx configuration is valid"
else
    echo "❌ Nginx configuration has errors"
    exit 1
fi

echo "✅ Entrypoint setup complete!"
echo "🌟 Starting services..."

# Start supervisor
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf