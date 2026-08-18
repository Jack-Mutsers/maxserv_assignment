FROM composer:2.6 AS composer

# Use the official PHP image as a base image to construct our own image from
FROM php:8.2.12-apache

# Install and enable mysql modules for PHP
RUN docker-php-ext-install mysqli pdo pdo_mysql && \
    docker-php-ext-enable mysqli pdo pdo_mysql

# Set an environment variable which contains the apache document root
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

# Update the apache configuration using the `APACHE_DOCUMENT_ROOT` environment variable
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf
