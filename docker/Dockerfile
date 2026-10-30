FROM php:8.2-apache

# * PDO MySQL para los DAO
RUN docker-php-ext-install pdo_mysql

# * Xdebug
RUN pecl install xdebug && docker-php-ext-enable xdebug
COPY docker/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini