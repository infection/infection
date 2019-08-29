#!/usr/bin/env bash

readonly INFECTION=../../../../${1}

set -e pipefail

if [ "$DRIVER" = "pcov" ]
then
    # `pcov` requires at least PHPUnit 8.0 (used by symfony/phpunit-bridge)
    export SYMFONY_PHPUNIT_VERSION="8.0"
fi

if [ "$DRIVER" = "phpdbg" ]
then
    phpdbg -qrr $INFECTION
else
    php $INFECTION
fi

diff -w expected-output.txt infection.log
