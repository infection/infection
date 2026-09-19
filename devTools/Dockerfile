ARG PHP_VERSION

FROM php:${PHP_VERSION}-cli-alpine

ARG XDEBUG_VERSION

RUN apk add --update --no-cache \
		linux-headers \
	;

# mbstring is compiled below only when the base image lacks it: PHP >= 8.6 no
# longer bundles it, and PHPUnit requires it.
RUN --mount=type=bind,from=ghcr.io/php/pie:bin,source=/pie,target=/usr/local/bin/pie \
    set -eux; \
    apk add --no-cache --virtual .build-deps \
        ${PHPIZE_DEPS} \
        build-base \
        libzip-dev \
        libtool \
        oniguruma-dev \
        unzip \
        zlib-dev \
    ; \
    docker-php-ext-configure zip; \
    docker-php-ext-install -j$(nproc) \
        pcntl \
    ; \
    php -m | grep -qx mbstring || docker-php-ext-install -j$(nproc) mbstring; \
    pie install --no-cache xdebug/xdebug:${XDEBUG_VERSION}; \
    rm -rf /root/.pie; \
    runDeps="$( \
        scanelf --needed --nobanner --format '%n#p' --recursive /usr/local/lib/php/extensions \
            | tr ',' '\n' \
            | sort -u \
            | awk 'system("[ -e /usr/local/lib/" $1 " ]") == 0 { next } { print "so:" $1 }' \
    )"; \
    apk add --no-cache --virtual .app-phpexts-rundeps ${runDeps}; \
    apk del .build-deps

RUN apk add --no-cache \
        ncurses \
        make \
        bash \
        expect \
        git \
        zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY memory-limit.ini xdebug.ini ${PHP_INI_DIR}/conf.d/

RUN adduser -h /opt/infection -s /bin/bash -D infection

USER infection

WORKDIR /opt/infection
