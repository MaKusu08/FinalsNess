# Stage 1: PHP-FPM with extensions
FROM php:8.2-fpm AS php

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libpq-dev \
    libicu-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    netcat-openbsd \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    pdo_mysql \
    pdo_pgsql \
    zip \
    intl \
    opcache \
    gd \
    bcmath \
    && pecl install apcu redis \
    && docker-php-ext-enable apcu redis

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/symfony

# Allow Composer to run as root (safe in Docker containers)
ENV COMPOSER_ALLOW_SUPERUSER=1

# Copy composer files first for better caching
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-autoloader --no-scripts --no-progress --no-interaction

# Copy application files
COPY . .

# Create a temporary .env file for build process only
RUN if [ ! -f .env ]; then \
        echo "APP_ENV=prod" > .env && \
        echo "APP_DEBUG=0" >> .env && \
        echo "APP_SECRET=build-temp-secret" >> .env && \
        echo "DATABASE_URL=sqlite:///:memory:" >> .env; \
    else \
        # Backup original .env and modify for build
        cp .env .env.backup && \
        sed -i 's/APP_ENV=.*/APP_ENV=prod/' .env && \
        sed -i 's/APP_DEBUG=.*/APP_DEBUG=0/' .env && \
        sed -i 's/DATABASE_URL=.*/DATABASE_URL="sqlite:\/\/\/:memory:"/' .env; \
    fi

# Set production environment variables for the build
ENV APP_ENV=prod
ENV APP_DEBUG=0
ENV DATABASE_URL="sqlite:///:memory:"

# Run composer install with scripts disabled first
RUN composer install --no-dev --no-progress --no-interaction --no-scripts && \
    composer dump-autoload --optimize

# Manually warm up the cache
RUN php bin/console cache:warmup --env=prod --no-debug || true

# Restore original .env if it was backed up
RUN if [ -f .env.backup ]; then mv .env.backup .env; fi

# Remove the build .env to prevent accidental use
RUN rm -f .env

# Set permissions
RUN chown -R www-data:www-data /var/www/symfony/var /var/www/symfony/public

# PHP-FPM configuration
COPY docker/php/php.ini /usr/local/etc/php/conf.d/symfony.ini

# Stage 2: Nginx
FROM nginx:mainline-alpine AS nginx

# Install supervisor and netcat for health checks
RUN apk add --no-cache supervisor netcat-openbsd curl bash

# Copy nginx configuration
COPY nginx.conf /etc/nginx/nginx.conf
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf

# Copy Symfony application from PHP stage
COPY --from=php /var/www/symfony /var/www/symfony

# Copy entrypoint script
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# Copy supervisor config
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Create required directories
RUN mkdir -p /var/log/supervisor

# Expose port
EXPOSE 80

# Set entrypoint
ENTRYPOINT ["/entrypoint.sh"]

# Start supervisor
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]