#!/usr/bin/env bash

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

    diff -u -w expected-output.txt infection.log
}
