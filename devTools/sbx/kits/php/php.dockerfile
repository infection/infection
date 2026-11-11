# Debian 13 is the compatibility baseline for workloads using this mixin.
# The kit frontend excludes unchanged base files from the overlay.
FROM debian:trixie-slim

SHELL ["/bin/bash", "-c"]

ARG PHP_VERSION=8.4

# PHP
RUN --mount=type=cache,target=/var/cache/apt,sharing=locked \
    set -o errexit -o nounset -o pipefail \
    && DEBIAN_FRONTEND=noninteractive apt-get update \
    && DEBIAN_FRONTEND=noninteractive apt-get install -y --no-install-recommends \
        ca-certificates \
        curl \
        gnupg \
    && curl -fsSL https://packages.sury.org/php/apt.gpg | gpg --dearmor > /usr/share/keyrings/php-sury.gpg \
    && . /etc/os-release \
    && echo "deb [signed-by=/usr/share/keyrings/php-sury.gpg] https://packages.sury.org/php/ ${VERSION_CODENAME} main" > /etc/apt/sources.list.d/sury-php.list \
    && DEBIAN_FRONTEND=noninteractive apt-get update \
    && DEBIAN_FRONTEND=noninteractive apt-get install -y --no-install-recommends \
        # CLI SAPI
        php${PHP_VERSION}-cli \
        # Extensions used by infection to not have to rely on polyfills
        php${PHP_VERSION}-curl \
        php${PHP_VERSION}-mbstring \
        php${PHP_VERSION}-xml \
        php${PHP_VERSION}-zip \
        # Coverage drivers
        php${PHP_VERSION}-pcov \
        php${PHP_VERSION}-xdebug \
        # Build tools required by PIE to install PHP extensions that compile locally
        autoconf \
        bison \
        gcc \
        libtool \
        make \
        php${PHP_VERSION}-dev \
        pkg-config \
        re2c \
        # Downloads files during sandbox usage
        wget \
    && echo 'xdebug.mode=coverage' > /etc/php/${PHP_VERSION}/cli/conf.d/xdebug.ini \
    && echo 'memory_limit=-1' > /etc/php/${PHP_VERSION}/cli/conf.d/memory-limit.ini \
    && rm -rf /tmp/*

COPY --from=composer/composer:2-bin /composer /usr/local/bin/composer

# PIE
# https://github.com/php/pie#one-liner
RUN set -o errexit -o nounset -o pipefail \
    && curl -fL --output /tmp/pie.phar https://github.com/php/pie/releases/latest/download/pie.phar \
    && mv /tmp/pie.phar /usr/local/bin/pie \
    && chmod +x /usr/local/bin/pie
