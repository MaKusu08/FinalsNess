# Stage 1: PHP-FPM with extensions
FROM php:8.3-fpm AS php

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

# Copy composer files first for better caching
COPY composer.json composer.lock ./
RUN COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev --no-autoloader --no-scripts --no-progress --no-interaction

# Copy application files
COPY . .

# Run composer install again with full dependencies
RUN COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev --no-progress --no-interaction && \
    COMPOSER_ALLOW_SUPERUSER=1 composer dump-autoload --optimize

# Set permissions
RUN chown -R www-data:www-data /var/www/symfony/var /var/www/symfony/public

# PHP-FPM configuration
COPY docker/php/php.ini /usr/local/etc/php/conf.d/symfony.ini

# Stage 2: Nginx
FROM nginx:mainline-alpine AS nginx

# Install supervisor to run both services
RUN apk add --no-cache supervisor

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