FROM php:7.1.33-fpm AS base
WORKDIR /var/www/html

RUN apt update -y
RUN DEBIAN_FRONTEND=noninteractive apt-get install --no-install-recommends -y \
    libicu-dev \
    libzip-dev \
    unzip \
    vim
RUN apt purge -y --auto-remove

RUN docker-php-ext-install \
    intl \
    mysqli \
    pdo \
    pdo_mysql \
    zip

# We could use the composer image directly here but using the php
# one guarantees we have the same version of php installed. Instead
# we pull the composer script from one of their images into this one.
FROM php:7.1.33-fpm AS build
WORKDIR /var/www/html

RUN apt update -y
RUN DEBIAN_FRONTEND=noninteractive apt-get install --no-install-recommends -y \
    libicu-dev \
    libzip-dev

RUN docker-php-ext-install \
    intl \
    zip

# Composer 2.3 dropped support for php <7.2, so specify the last
# 2.2 version here.
COPY --from=composer:2.2 /usr/bin/composer /usr/bin/composer
COPY bropkg/composer.json .
RUN composer install

COPY bropkg .
RUN composer dumpautoload --optimize

FROM base AS final

COPY --from=build /var/www/html /var/www/html
COPY secrets/.env /var/www/html/config/.env

RUN chown -R www-data:www-data /var/www/html
RUN chmod 640 /var/www/html/config/.env
