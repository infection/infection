#!/usr/bin/env bash

set -e pipefail

readonly INFECTION="../../../bin/infection --coverage=infection-coverage --skip-initial-tests"

if [ "$DRIVER" = "phpdbg" ]
then
    phpdbg -qrr $INFECTION
else
    php $INFECTION
fi

diff expected-output_phpunit.txt infection.log

if [ -f "has_run" ]
then
    echo "The PHPUnit tests should not have been executed"
    exit 1;
fi
