FROM composer:2.3.7 AS composerimage

################################################################################

FROM php:8.0-fpm AS base

# Debian dep and Apache2 config
RUN apt-get update && \
    apt-get install -y systemd libzip-dev zlib1g-dev zip unzip sendmail libpng-dev libicu-dev g++ && \
    apt-get install -y --yes --force-yes cron gettext openssl libc-client-dev libkrb5-dev  libxml2-dev \
        libfreetype6-dev libgd-dev libmcrypt-dev bzip2 libbz2-dev libtidy-dev \
        libcurl4-openssl-dev libz-dev libmemcached-dev libxslt-dev && \
    docker-php-ext-configure zip --with-libzip && \
    docker-php-ext-install pdo pdo_mysql zip opcache mbstring zip && \
    docker-php-ext-configure intl && \
    docker-php-ext-install intl mysqli pdo pdo_mysql && \
    docker-php-ext-configure gd --with-freetype-dir=/usr --with-jpeg-dir=/usr && \
    docker-php-ext-install gd && \
    docker-php-ext-enable pdo_mysql && \
    rm -rf /var/lib/apt/lists/* && \
    rm -rf /etc/apache2/sites-available/* && \
    rm -rf /etc/apache2/sites-enabled/* && \
    echo "ServerName localhost" >> /etc/apache2/apache2.conf

COPY docker/php.ini /usr/local/etc/php/php.ini

# Conf Apache2
COPY docker/app.conf /etc/apache2/sites-available/app.conf
RUN usermod -u 1000 www-data && a2ensite app.conf && a2enmod rewrite

################################################################################

# Install Composer
FROM base AS deps
COPY --from=composerimage /usr/bin/composer /usr/bin/composer

RUN rm -rf /var/www/* && mkdir -p /var/www/app && chown www-data:www-data /var/www/app
USER www-data
WORKDIR /var/www/app
COPY --chown=www-data:www-data composer* /var/www/app/
RUN composer install --no-cache --no-scripts --prefer-dist

################################################################################

FROM base AS prod

COPY --from=deps /var/www /var/www

# Copy app files
COPY --chown=www-data:www-data . /var/www/app/
COPY --chown=www-data:www-data docker/.env /var/www/app/.env

WORKDIR /var/www/app

VOLUME /var/www/app

# EXPOSE 80
CMD ["/usr/sbin/apache2ctl", "-D", "FOREGROUND"]

################################################################################

FROM prod AS test

COPY --from=symfonycorp/cli /symfony /usr/local/bin/symfony
RUN php bin/console c:c

################################################################################

FROM prod