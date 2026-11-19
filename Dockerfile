FROM php:8.3-apache

# Instala el controlador PDO de MySQL utilizado por los archivos api_*.php.
RUN docker-php-ext-install pdo_mysql

WORKDIR /var/www/html
COPY app/ /var/www/html/
