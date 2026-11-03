FROM php:8.2-apache

# * PDO MySQL, Xdebug y mod_rewrite en una sola capa
RUN docker-php-ext-install pdo_mysql \
    && pecl install xdebug \
    && a2enmod rewrite

COPY xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini
COPY apache.conf /etc/apache2/sites-available/000-default.conf
