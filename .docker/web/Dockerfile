FROM php:7.3-apache

RUN apt update && apt install -y git zip

COPY composer.json /var/www/html/
COPY composer.lock /var/www/html/

RUN pecl config-set verbose 0
RUN docker-php-ext-install mysqli pdo pdo_mysql
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer --version=1.10.16 && composer install

COPY . /var/www/html/
COPY .docker/vhost.conf /etc/apache2/sites-available/000-default.conf

RUN chown -R www-data:www-data /var/www/html && a2enmod headers && a2enmod rewrite && sed -ri -e 's/^([ \t]*)(<\/VirtualHost>)/\1\tHeader set Access-Control-Allow-Origin "*"\n\1\2/g' /etc/apache2/sites-available/*.conf

EXPOSE 80
