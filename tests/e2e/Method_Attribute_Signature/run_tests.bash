#!/usr/bin/env bash

cd "$(dirname "$0")"

# PHPUnit 13 (php-code-coverage 14.1.5+) is required to reproduce the issue
if [ $(php -r 'echo version_compare(PHP_VERSION, "8.4.1", "<");') ]; then
    echo "Skipping test it needs PHP 8.4.1 or higher (found $(php -r 'echo PHP_VERSION;'))"
    exit 0
fi

readonly INFECTION=../../../${1}

set -eo pipefail

if [ "$DRIVER" = "phpdbg" ]
then
    phpdbg -qrr $INFECTION
else
    php $INFECTION
fi

if [ -n "$GOLDEN" ]; then
    cp -v infection.log expected-output.txt
fi

diff -u --ignore-all-space expected-output.txt infection.log
