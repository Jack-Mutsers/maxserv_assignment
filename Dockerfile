FROM composer:2.6 AS composer-binary

FROM php:8.4.12-cli AS dependencies
COPY --from=composer-binary /usr/bin/composer /usr/bin/composer
RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip \
    && rm -rf /var/lib/apt/lists/*

# Install dependencies in Linux so local path packages are copied into vendor
# instead of becoming Windows junctions.
WORKDIR /app
COPY composer.json composer.lock ./
COPY packages ./packages
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Use the official PHP image as a base image to construct our own image from
FROM php:8.4.12-apache

# Install and enable mysql modules for PHP
RUN docker-php-ext-install mysqli pdo pdo_mysql && \
    docker-php-ext-enable mysqli pdo pdo_mysql

# Enable step debugging from the Docker container to the local IDE.
RUN pecl install xdebug && \
    docker-php-ext-enable xdebug

COPY docker/xdebug.ini /usr/local/etc/php/conf.d/99-xdebug.ini

WORKDIR /var/www/html
COPY . ./
COPY --from=dependencies /app/vendor ./vendor

# Set an environment variable which contains the apache document root
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

# Update the apache configuration using the `APACHE_DOCUMENT_ROOT` environment variable
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf
