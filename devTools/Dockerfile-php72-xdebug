FROM php:7.2-cli

RUN pecl install xdebug && docker-php-ext-enable xdebug
RUN apt-get update && apt-get install -y --no-install-recommends \
    expect git zip \
    && rm -rf /var/lib/apt/lists/*

RUN curl --silent --show-error https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN useradd --home-dir /opt/infection --shell /bin/bash infection

USER infection
