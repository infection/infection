#!/usr/bin/env bash

cd "$(dirname "$0")"

set -eo pipefail

readonly INFECTION=../../../${1}

rm -rf var
mkdir var

if php -r 'exit(extension_loaded("pcov") ? 0 : 1);'; then
    environment=pcov
else
    environment=xdebug
fi

if XDEBUG_MODE=coverage php -r 'exit(extension_loaded("xdebug") ? 0 : 1);'; then
    if [ "$environment" = "pcov" ]; then
        environment=pcov-with-xdebug
    fi
fi

XDEBUG_MODE=coverage php vendor/bin/phpunit --coverage-xml=var/coverage
XDEBUG_MODE=coverage php -d memory_limit=-1 "$INFECTION" --coverage=var/coverage --no-progress

php verify.php var/infection.debug.jsonl "expected/$environment.json"
