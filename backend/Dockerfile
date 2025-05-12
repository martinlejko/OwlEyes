FROM composer:latest AS composer

# Set working directory
WORKDIR /app

# Copy composer files
COPY composer.json ./

# Install dependencies and generate composer.lock
RUN composer install --no-scripts --no-autoloader

FROM php:8.2-fpm

# Install dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    git \
    zip \
    unzip \
    libzip-dev \
    libicu-dev \
    libpq-dev \
    && docker-php-ext-install \
    pdo_pgsql \
    zip \
    intl

# Configure PHP
RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . .

# Copy vendor directory and composer.lock from composer stage
COPY --from=composer /app/vendor /var/www/html/vendor
COPY --from=composer /app/composer.lock /var/www/html/composer.lock

# Install Composer for dump-autoload
COPY --from=composer /usr/bin/composer /usr/bin/composer

# Generate autoloader
RUN composer dump-autoload --optimize

# Set permissions
RUN chown -R www-data:www-data /var/www/html

# Use the default PHP-FPM command
CMD ["php-fpm"] 