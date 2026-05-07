FROM docker/sandbox-templates:codex-docker

# The base image runs as the non-root `agent` user by default.
# Switch to root for all system-level package installations.
USER root

ARG PHP_VERSION=8.4

# PHP
RUN --mount=type=cache,target=/var/cache/apt,sharing=locked \
    set -o errexit -o nounset -o pipefail \
    && apt-get update \
    && apt-get install -y --no-install-recommends \
        # CLI SAPI
        php${PHP_VERSION}-cli \
        # Extensions used by infection to not have to rely on polyfills
        php${PHP_VERSION}-mbstring \
        php${PHP_VERSION}-xml \
        php${PHP_VERSION}-zip \
        # Coverage driver
        php${PHP_VERSION}-xdebug \
        # CA bundle used by curl for HTTPS downloads
        ca-certificates \
        # Downloads the container-structure-test and PIE PHARs during image build
        curl \
    && echo 'xdebug.mode=coverage' > /etc/php/${PHP_VERSION}/cli/conf.d/xdebug.ini \
    && rm -rf /tmp/*

COPY --from=composer/composer:2-bin /composer /usr/local/bin/composer

# PIE
# https://github.com/php/pie#one-liner
RUN set -o errexit -o nounset -o pipefail \
    && curl -fL --output /tmp/pie.phar https://github.com/php/pie/releases/latest/download/pie.phar \
    && mv /tmp/pie.phar /usr/local/bin/pie \
    && chmod +x /usr/local/bin/pie

ARG CONTAINER_STRUCTURE_TEST_VERSION=1.22.1
ARG TARGET_ARCH

# container-structure-test
RUN set -o errexit -o nounset -o pipefail \
    && target_arch="${TARGET_ARCH:-$(dpkg --print-architecture)}" \
    && case "${target_arch}" in \
        x86_64) target_arch=amd64 ;; \
        aarch64) target_arch=arm64 ;; \
    esac \
    && curl --location --fail --show-error --silent \
        --output /usr/local/bin/container-structure-test \
        "https://github.com/GoogleContainerTools/container-structure-test/releases/download/v${CONTAINER_STRUCTURE_TEST_VERSION}/container-structure-test-linux-${target_arch}" \
    && chmod +x /usr/local/bin/container-structure-test

# Allow telemetry
ENV NO_PROXY="${NO_PROXY:+${NO_PROXY},}host.docker.internal,169.254.1.1,localhost,127.0.0.1,::1,gateway.docker.internal"
ENV no_proxy="$NO_PROXY"

# Restore the default non-root user that the sandbox agent runs as.
USER agent
