# Composer source image
FROM composer:2 AS composer

# PHP-FPM runtime image
FROM php:8.2-fpm-alpine

WORKDIR /var/www

# System dependencies and PHP extensions
RUN apk add --no-cache \
    bash \
    git \
    unzip \
    curl \
    libzip-dev \
    oniguruma-dev \
    postgresql-dev \
    nodejs \
    npm \
    && docker-php-ext-install pdo pdo_pgsql mbstring zip

# Copy Composer binary from the official Composer image
COPY --from=composer /usr/bin/composer /usr/bin/composer

# Verify Composer during build
RUN composer --version

# Copy application files
COPY . .

# Install Laravel dependencies
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Laravel writable directories
RUN chown -R www-data:www-data /var/www \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

EXPOSE 9000

# PHP-FPM + Laravel scheduler + queue worker
CMD ["sh", "-c", "php-fpm & while :; do php artisan schedule:run --verbose --no-interaction; sleep 60; done & php artisan queue:work --tries=3 --timeout=90"]