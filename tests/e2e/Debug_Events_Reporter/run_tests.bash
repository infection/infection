#!/usr/bin/env bash

set -eo pipefail

cd "$(dirname "$0")"

readonly INFECTION=../../../${1}

mkdir -p var
printf 'Previous run\n' > var/events.jsonl

if [ "${DRIVER:-}" = "phpdbg" ]; then
    phpdbg -qrr "$INFECTION" --no-progress --log-verbosity=none
else
    php "$INFECTION" --no-progress --log-verbosity=none
fi

php verify.php var/events.jsonl
