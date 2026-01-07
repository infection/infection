#!/usr/bin/env bash

cd "$(dirname "$0")"

set -e pipefail

readonly INFECTION="../../../bin/infection --coverage=infection-coverage --with-uncovered"
readonly PHPUNIT="vendor/bin/phpunit  --coverage-xml=infection-coverage/coverage-xml --log-junit=infection-coverage/junit.xml"

if [ "$DRIVER" = "phpdbg" ]
then
    phpdbg -qrr $PHPUNIT
else
    export XDEBUG_MODE=coverage
    php $PHPUNIT
fi

if [ "$DRIVER" = "phpdbg" ]
then
    phpdbg -qrr $INFECTION
else
    php $INFECTION
fi

if [ -n "$GOLDEN" ]; then
    cp -v infection.log expected-output_phpunit.txt
fi

diff -u expected-output_phpunit.txt infection.log

if [ -f "infection-cache/infection/phpunit.junit.xml" ]
then
    echo "Infection should not generate phpunit.junit.xml if path with existing files has been provided"
    exit 1;
fi

if [ -d "infection-cache/infection/coverage-xml" ]
then
    echo "Infection should not generate coverage-xml if path with existing files has been provided"
    exit 1;
fi
