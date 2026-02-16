# Use official PHP-FPM image
FROM php:8.2-fpm-alpine

# Set working directory
WORKDIR /var/www

# Install system dependencies and PHP extensions
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

# Install Composer globally
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer

# Copy entire project first (artisan must exist for package:discover)
COPY . .

# Install PHP dependencies
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Set permissions
RUN chown -R www-data:www-data /var/www

# Expose PHP-FPM port
EXPOSE 9000

# Start PHP-FPM + scheduler + queue for dev
CMD ["sh", "-c", "php-fpm & while :; do php artisan schedule:run --verbose --no-interaction; sleep 60; done & php artisan queue:work --tries=3 --timeout=90"]
