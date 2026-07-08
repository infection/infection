#!/usr/bin/env bash

cd "$(dirname "$0")"

readonly INFECTION=../../../${1}

set -e pipefail

rm -rf var/*

if [ "${DRIVER:-}" != "" ] && [ "${DRIVER:-}" != "xdebug" ]
then
    # This regression requires Composer XdebugHandler's temporary php.ini.
    # PCOV and phpdbg do not exercise that path.
    exit 0
fi

if ! XDEBUG_MODE=coverage php -r 'exit(extension_loaded("xdebug") ? 0 : 1);'
then
    if [ "${DRIVER:-}" = "xdebug" ]
    then
        echo "PHPStan_Memory_Limit requires Xdebug to be installed and loadable with XDEBUG_MODE=coverage." >&2
        exit 1
    fi

    # Non-Xdebug environments do not exercise this regression.
    exit 0
fi

if ! XDEBUG_MODE=coverage php -d memory_limit=-1 restart-check.php
then
    echo "PHPStan_Memory_Limit requires Composer XdebugHandler to restart PHP with memory_limit=-1 and a loaded php.ini." >&2
    exit 1
fi

XDEBUG_MODE=coverage php -d memory_limit=-1 $INFECTION --no-progress --threads=1

if [ -n "$GOLDEN" ]; then
    cp -v infection.log expected-output.txt
fi;

diff -u --ignore-all-space expected-output.txt infection.log
