FROM php:8.4.12-fpm


# Installation des dépendances système
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libicu-dev \
    libpq-dev \
    && docker-php-ext-install pdo pdo_mysql zip intl opcache

# Installation de Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configuration du working directory
WORKDIR /var/www/html

# Permissions
RUN chown -R www-data:www-data /var/www/html

USER www-data

EXPOSE 9000