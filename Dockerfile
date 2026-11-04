FROM php:8.2-apache

# * PDO MySQL, Xdebug y mod_rewrite en una sola capa
RUN docker-php-ext-install pdo_mysql \
    && pecl install xdebug \
    && a2enmod rewrite

# * Composer: descarga el instalador oficial y deja el programa en /usr/local/bin
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# ! Rutas desde la raíz del proyecto (context: .), no desde la carpeta Docker/
COPY Docker/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini
COPY Docker/apache.conf /etc/apache2/sites-available/000-default.conf
