FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    libpq-dev \
    git \
    unzip \
    && docker-php-ext-install pdo pdo_pgsql

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN a2enmod rewrite
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf

WORKDIR /app

# Copy composer files first
COPY composer.json composer.lock ./
RUN composer install --no-scripts --no-autoloader

# Copy the rest of the application
COPY . .

# Complete composer install with scripts and autoloader
RUN composer dump-autoload --optimize

# Set permissions for var directory
RUN chown -R www-data:www-data /app/var && \
    chmod -R 775 /app/var