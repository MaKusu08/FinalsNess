FROM php:8.3-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libpq-dev \
    libicu-dev \
    && docker-php-ext-install \
    pdo_mysql \
    pdo_pgsql \
    zip \
    intl \
    opcache

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Configure Apache for Symfony
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf

# Set working directory
WORKDIR /var/www/html

# Allow Composer to run as root
ENV COMPOSER_ALLOW_SUPERUSER=1

# Copy ALL application files first
COPY . .

# Create .env file for the build process
RUN echo "APP_ENV=prod" > .env && \
    echo "APP_DEBUG=0" >> .env && \
    echo "APP_SECRET=build-dummy-secret-for-container" >> .env && \
    echo "DATABASE_URL=sqlite:///:memory:" >> .env

# Run composer install (all files are now present)
RUN composer install --no-dev --no-progress --no-interaction && \
    composer dump-autoload --optimize

# Set permissions
RUN chown -R www-data:www-data /var/www/html/var /var/www/html/public

# Remove build .env (will be created at runtime)
RUN rm -f .env

EXPOSE 80

# Simple start command
CMD ["apache2-foreground"]