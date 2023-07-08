FROM php:8.2-apache
MAINTAINER aleksandr <aleksandr.kravchuk.os@gmail.com>

RUN apt-get update && \
    apt-get install -y openssl && \
    apt-get install -y wget && \
    apt-get install -y libz-dev && \
    apt-get install -y vim && \
    apt-get install -y mc && \
    apt-get install -y curl && \
    apt-get install -y apache2

RUN apt-get update && apt-get install -y libpng-dev

RUN docker-php-ext-install gd
RUN docker-php-ext-install exif
RUN #docker-php-ext-install

# Install libzip package and development files
RUN apt-get update && apt-get install -y \
    libzip-dev

# Restart Apache service
RUN service apache2 restart

COPY entrypoint.sh /sbin/entrypoint.sh

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --version=2.5.8 --install-dir=/usr/local/bin --filename=composer

COPY ./configs/apache2.conf /etc/apache2/apache2.conf
COPY ./configs/default.conf /etc/apache2/sites-enabled/default.conf
COPY ./configs/php.ini  /usr/local/etc/php/php.ini
COPY ./configs/php.ini  /usr/local/etc/php/php.ini-development
COPY ./configs/php.ini  /usr/local/etc/php/php.ini-production

RUN docker-php-ext-install pdo_mysql

# Install MySQL client
RUN apt-get update && \
    apt-get install -y default-mysql-client

# Enable necessary Apache modules
RUN a2enmod rewrite

# Restart Apache service
RUN service apache2 restart

RUN a2enmod proxy proxy_http proxy_html substitute deflate xml2enc rewrite ssl
USER root
WORKDIR /var/www/
EXPOSE 80 443 22

# By default, simply start apache.
CMD ["/sbin/entrypoint.sh"]
