FROM composer AS composerimage

################################################################################
FROM php:8.0-fpm AS base

RUN apt-get update -y && apt-get install -y --yes --force-yes \
    libc-client-dev libcurl4-openssl-dev libfreetype6-dev libicu-dev libjpeg-dev libkrb5-dev libmagickwand-dev libmcrypt-dev libonig-dev libpng-dev libssl-dev libtidy-dev libwebp-dev libxml2-dev libxslt-dev libyaml-dev libzip-dev libzstd-dev \
    libgd-dev libbz2-dev libz-dev libmemcached-dev bzip2 zlib1g-dev \
    curl mariadb-client wget zip unzip cron gettext openssl g++ sendmail nano && \
    pecl install igbinary imagick yaml && \
    docker-php-ext-configure zip && \
    docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp && \
    docker-php-ext-configure curl --with-curl && \
    docker-php-ext-configure gettext --with-gettext && \
    docker-php-ext-configure intl --enable-intl && \
    docker-php-ext-configure pcntl --enable-pcntl && \
    docker-php-ext-configure pdo_mysql --with-pdo-mysql && \
    docker-php-ext-configure mbstring --enable-mbstring && \
    docker-php-ext-configure soap --enable-soap && \
    docker-php-ext-install curl gd gettext intl pcntl mbstring mysqli opcache pdo pdo_mysql soap zip && \
    docker-php-ext-enable igbinary imagick yaml pdo_mysql && \
    rm -rf /var/lib/apt/lists/

COPY ./docker/local/api/env/www.conf /usr/local/etc/php-fpm.d/www.conf
COPY ./docker/local/api/env/php.ini /usr/local/etc/php/php.ini

COPY --from=composerimage /usr/bin/composer /usr/bin/composer
# COPY --from=symfonycorp/cli /symfony /usr/local/bin/symfony

################################################################################

# Install Composer
FROM base AS deps

RUN rm -rf /var/www/* && mkdir -p /var/www/app && chown -hR www-data:www-data /var/www/app

# Copy composer files
COPY --chown=www-data:www-data ./composer.json /var/www/app/composer.json

USER www-data
WORKDIR /var/www/app
RUN composer install --no-cache --no-scripts --prefer-dist

################################################################################

FROM base AS prod

COPY --from=deps /var/www /var/www

# Copy app files
COPY --chown=www-data:www-data . /var/www/app/
COPY --chown=www-data:www-data ./docker/local/api/env/.env /var/www/app/.env
COPY --chown=www-data:www-data ./docker/local/api/env/.env.test /var/www/app/.env.test

WORKDIR /var/www/app
VOLUME /var/www/app
CMD ["php-fpm"]

################################################################################

FROM prod AS test
RUN symfony check:security

USER www-data
RUN php bin/console c:c

################################################################################

FROM prod