#!/usr/bin/env bash

if [ $(php -r 'echo version_compare(PHP_VERSION, "8.3.0", "<");') ]; then
    echo "Skipping test it needs PHP 8.3.0 or higher (found $(php -r 'echo PHP_VERSION;'))"
    exit 0
fi

readonly INFECTION=../../../${1}

set -e pipefail

if [ "$DRIVER" = "phpdbg" ]
then
    phpdbg -qrr $INFECTION
else
    php $INFECTION
fi

diff --ignore-all-space expected-output.txt infection.log
