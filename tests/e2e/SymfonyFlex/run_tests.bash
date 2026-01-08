#!/usr/bin/env bash

cd "$(dirname "$0")"

if [ "$DRIVER" = "pcov" ]
then
    # `pcov` requires at least PHPUnit 8.0 (used by symfony/phpunit-bridge)
    export SYMFONY_PHPUNIT_VERSION="8.0"
fi

set -e

run () {
    local INFECTION=${1}
    local PHPARGS=${2}

    if [ "$DRIVER" = "phpdbg" ]
    then
        phpdbg $PHPARGS -qrr $INFECTION
    else
        php $PHPARGS $INFECTION
    fi

    if [ -n "$GOLDEN" ]; then
        cp -v infection.log expected-output.txt
    fi

    diff -u --ignore-all-space expected-output.txt infection.log
}
