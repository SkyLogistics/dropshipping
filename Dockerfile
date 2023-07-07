FROM php:8.2-apache
MAINTAINER aleksandr <aleksandr.kravchuk.os@gmail.com>

ENV OS_LOCALE="en_US.UTF-8"
RUN apt-get update && apt-get install -y locales && locale-gen ${OS_LOCALE}
ENV LANG=${OS_LOCALE} \
    LANGUAGE=${OS_LOCALE} \
    LC_ALL=${OS_LOCALE} \
    DEBIAN_FRONTEND=noninteractive \
    APACHE_CONF_DIR=/etc/apache2 \
    PHP_CONF_DIR=/etc/php/8.2 \
    PHP_DATA_DIR=/var/lib/php

COPY entrypoint.sh /sbin/entrypoint.sh

RUN	\
	BUILD_DEPS='software-properties-common' \
    && dpkg-reconfigure locales \
	&& apt-get install --no-install-recommends -y $BUILD_DEPS \
	&& apt-get install --no-install-recommends -y openssl \
	&& apt-get install -y wget \
	&& add-apt-repository -y ppa:ondrej/php \
	&& add-apt-repository -y ppa:ondrej/apache2 \
	&& apt-get update \
    && apt-get install -y libz-dev vim mc curl apache2 supervisor libapache2-mod-php php-cli php-readline php-mbstring php-zip php-intl php-xml php-json php-curl php-gd php-pgsql php-mysql php-pear \
    # Apache settings
    && cp /dev/null ${APACHE_CONF_DIR}/conf-available/other-vhosts-access-log.conf \
    && rm ${APACHE_CONF_DIR}/sites-enabled/000-default.conf ${APACHE_CONF_DIR}/sites-available/000-default.conf \
    && a2enmod rewrite php8.2* \
	# Install composer
	&& curl -sS https://getcomposer.org/installer | php -- --version=2.5.8 --install-dir=/usr/local/bin --filename=composer \
	# Cleaning
	&& apt-get purge -y --auto-remove $BUILD_DEPS \
	&& apt-get autoremove -y \
	&& rm -rf /var/lib/apt/lists/* \
	# Forward request and error logs to docker log collector
	&& ln -sf /dev/stdout /var/log/apache2/access.log \
	&& ln -sf /dev/stderr /var/log/apache2/error.log \
	&& chmod 755 /sbin/entrypoint.sh \
	&& chmod 777 /etc/apache2/mods-enabled \
	&& chmod 777 /var/lib/apache2 \
	&& chown www-data:www-data ${PHP_DATA_DIR} -Rf

COPY ./configs/apache2.conf ${APACHE_CONF_DIR}/apache2.conf
COPY ./configs/default.conf ${APACHE_CONF_DIR}/sites-enabled/default.conf
COPY ./configs/php.ini  ${PHP_CONF_DIR}/apache2/conf.d/custom.ini

RUN a2enmod proxy proxy_http proxy_html substitute deflate xml2enc rewrite ssl

USER root
#RUN chmod -R 777 /var/www/html/
#RUN chmod -R 777 /var/www/sky-docker/storage
#RUN chmod -R 777 /var/www/sky-docker/bootstrap
#RUN chmod -R 777 /var/www/sky-docker/resources

WORKDIR /var/www/
#USER www-data



EXPOSE 80 443 22

CMD ["/usr/bin/supervisord"]

# By default, simply start apache.
CMD ["/sbin/entrypoint.sh"]



#RUN a2enmod rewrite
#RUN a2enmod ssl

#CMD ["cd /var/www/sky-docker && composer install"]
#
#RUN apt install nodejs
#
#RUN apt install npm
#
#CMD ["npm install"]
