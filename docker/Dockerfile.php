FROM php:7.1.33-fpm AS base
WORKDIR /var/www/html

RUN apt update -y
RUN DEBIAN_FRONTEND=noninteractive apt-get install --no-install-recommends -y \
    cron \
    git \
    libicu-dev \
    libzip-dev \
    procps \
    python3-pip \
    python3-setuptools \
    unzip \
    vim
RUN apt purge -y --auto-remove

RUN docker-php-ext-install \
    intl \
    mysqli \
    pdo \
    pdo_mysql \
    zip

# Install an initial version of zkg. This gets updated by cron
# every night before updating the packages list.
RUN pip3 install GitPython semantic-version zkg

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

# Build a final image from the above parts.
FROM base AS final

COPY --from=build --chown=www-data:www-data /var/www/html /var/www/html
COPY --chown=www-data:www-data --chmod=640 secrets/.env /var/www/html/config/.env

# zeek-package-ci is used by the cronjob to sanity check zeek packages. On the image,
# it's stored in /usr/local/bin/bro-package-ci. We explicitly pin to version 0.4.0
# which is the version the existing live site is using. The version on 'master' has
# some problems with the dns_resolution check over-matching.
RUN python3 -m pip install 'bro-package-ci@git+https://github.com/zeek/zeek-package-ci@1117e24fd80f03167ca36749bf5a246a02d86178'

COPY --chmod=755 cronjob/bro-pkg-web-updater.php /usr/local/sbin
COPY --chmod=755 cronjob/bro-pkg-web-cron.sh /etc/cron.daily

# Override the existing entrypoint script so that cron can start up too.
COPY --chmod=700 docker/php-entrypoint.sh /
ENTRYPOINT ["/php-entrypoint.sh"]
CMD ["php-fpm"]
