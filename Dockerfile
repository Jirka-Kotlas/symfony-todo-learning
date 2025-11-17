FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN a2enmod rewrite
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf
RUN service apache2 restart

WORKDIR /app
COPY . .

RUN composer install