#!/usr/bin/env bash

cd "$(dirname "$0")"

set -eo pipefail

readonly INFECTION=../../../${1}

rm -rf var
mkdir var

if [ "${DRIVER:-}" = "phpdbg" ]; then
    phpdbg -qrr "$INFECTION" --coverage=coverage --no-progress
else
    XDEBUG_MODE=coverage php -d memory_limit=-1 "$INFECTION" --coverage=coverage --no-progress
fi

php verify.php var/infection.debug.jsonl
