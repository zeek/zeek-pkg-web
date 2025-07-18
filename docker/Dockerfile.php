FROM php:8.1.33-fpm AS base
WORKDIR /var/www/html

ENV DEBIAN_FRONTEND=noninteractive

RUN apt-get update -y \
 && apt-get install --no-install-recommends -y \
    cron=3.0pl1-162 \
    git=1:2.39.5-0+deb12u2 \
    libicu-dev=72.1-3+deb12u1 \
    libzip-dev=1.7.3-1+b1 \
    procps=2:4.0.2-3 \
    python3-git=3.1.30-1+deb12u2 \
    python3-pip=23.0.1+dfsg-1 \
    python3-semantic-version=2.9.0-2 \
    python3-setuptools=66.1.1-1+deb12u1 \
    unzip=6.0-28 \
 && apt-get clean \
 && rm -rf /var/lib/apt/lists/* \
 # The source packages for these extensions come with the php docker image
 # so they're fixed per the version of php we're using. There's no reason
 # (or way) to specify the versions here.
 && docker-php-ext-install \
    intl \
    mysqli \
    pdo \
    pdo_mysql \
    zip \
 # Install an initial version of zkg. This gets updated by cron
 # every night before updating the packages list.
 && pip3 install --no-cache-dir --break-system-packages zkg==3.1.0

# We could use the composer image directly here but using the php
# one guarantees we have the same version of php installed. Instead
# we pull the composer script from one of their images into this one.
FROM base AS final
WORKDIR /var/www/html

ENV DEBIAN_FRONTEND=noninteractive

RUN apt-get update -y \
 && apt-get install --no-install-recommends -y \
    libicu-dev=72.1-3+deb12u1 \
    libzip-dev=1.7.3-1+b1 \
 && apt-get clean \
 && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install \
    intl \
    zip

COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer
COPY bropkg/composer.json .
#RUN composer install

COPY bropkg .
#RUN composer dumpautoload --optimize

# # Build a final image from the above parts.
# FROM base AS final

# COPY --from=build --chown=www-data:www-data /var/www/html /var/www/html
# COPY --chown=www-data:www-data --chmod=640 secrets/.env /var/www/html/config/.env

# # zeek-package-ci is used by the cronjob to sanity check zeek packages. On the image,
# # it's stored in /usr/local/bin/bro-package-ci. We explicitly pin to version 0.4.0
# # which is the version the existing live site is using. The version on 'master' has
# # some problems with the dns_resolution check over-matching.
# RUN python3 -m pip install --no-cache-dir --break-system-packages 'bro-package-ci@git+https://github.com/zeek/zeek-package-ci@1117e24fd80f03167ca36749bf5a246a02d86178'

# COPY --chmod=755 cronjob/bro-pkg-web-updater.php /usr/local/sbin
# COPY --chmod=755 cronjob/bro-pkg-web-cron.sh /etc/cron.daily/bro-pkg-web-cron

# # Override the existing entrypoint script so that cron can start up too.
# COPY --chmod=700 docker/php-entrypoint.sh /
# ENTRYPOINT ["/php-entrypoint.sh"]
# CMD ["php-fpm"]
