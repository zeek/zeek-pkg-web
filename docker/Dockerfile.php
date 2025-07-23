FROM php:7.1.33-fpm AS base
WORKDIR /var/www/html

ENV DEBIAN_FRONTEND=noninteractive
ENV PYTHON_VERSION=3.13.6

# # php:7.1.33-fpm is based on Debian 10/Buster, which doesn't have paths in
# # the default apt repos anymore. Swap over to archive.debian.org.
RUN sed -i 's/deb\.debian/archive.debian/g' /etc/apt/sources.list \
    && sed -i 's/security\.debian/archive.debian/g' /etc/apt/sources.list

RUN apt-get update -y \
 && apt-get install --no-install-recommends -y \
    cron=3.0pl1-134+deb10u1 \
    git=1:2.20.1-2+deb10u9 \
    libicu-dev=63.1-6+deb10u3 \
    libzip-dev=1.5.1-4 \
    pipx=0.12.1.0-1 \
    procps=2:3.3.15-2 \
    python3-venv=3.7.3-1 \
    unzip=6.0-23+deb10u3 \
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
    zip

# Install 'uv' to get a newer version of Python than is available on the
# base image.
# This is needed because of the pipe in the next command.
SHELL ["/bin/bash", "-o", "pipefail", "-c"]
RUN curl -LsSf "https://astral.sh/uv/install.sh" | sh \
 && echo ". ${HOME}/.local/bin/env" >> "${HOME}/.profile" \
 && . "${HOME}/.local/bin/env" \
 && uv python install "${PYTHON_VERSION}" \
 && uv venv "${HOME}/uv-venv" \
 && . "${HOME}/uv-venv/bin/activate" \
 && uv pip install zkg==3.1.0

# We could use the composer image directly here but using the php
# one guarantees we have the same version of php installed. Instead
# we pull the composer script from one of their images into this one.
FROM php:7.1.33-fpm AS build
WORKDIR /var/www/html

ENV DEBIAN_FRONTEND=noninteractive

# # php:7.1.33-fpm is based on Debian 10/Buster, which doesn't have paths in
# # the default apt repos anymore. Swap over to archive.debian.org.
RUN sed -i 's/deb\.debian/archive.debian/g' /etc/apt/sources.list \
    && sed -i 's/security\.debian/archive.debian/g' /etc/apt/sources.list

RUN apt-get update -y \
 && apt-get install --no-install-recommends -y \
    libicu-dev=63.1-6+deb10u3 \
    libzip-dev=1.5.1-4 \
 && apt-get clean \
 && rm -rf /var/lib/apt/lists/*

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

# # zeek-package-ci is used by the cronjob to sanity check zeek packages. On the image,
# # it's stored in /usr/local/bin/bro-package-ci. We explicitly pin to version 0.4.0
# # which is the version the existing live site is using. The version on 'master' has
# # some problems with the dns_resolution check over-matching.
RUN . "${HOME}/.local/bin/env" \
 && . "${HOME}/uv-venv/bin/activate" \
 && uv pip install --no-cache-dir "bro-package-ci@git+https://github.com/zeek/zeek-package-ci@1117e24fd80f03167ca36749bf5a246a02d86178"

COPY --chmod=755 cronjob/bro-pkg-web-updater.php /usr/local/sbin
COPY --chmod=755 cronjob/bro-pkg-web-cron.sh /etc/cron.daily/bro-pkg-web-cron

# Override the existing entrypoint script so that cron can start up too.
COPY --chmod=700 --chown=www-data:www-data docker/php-entrypoint.sh /
ENTRYPOINT ["/php-entrypoint.sh"]
CMD ["php-fpm"]
