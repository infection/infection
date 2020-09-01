#!/usr/bin/env bash

set -e pipefail

readonly INFECTION="../../../bin/infection --coverage=infection-coverage"

if [ "$DRIVER" = "phpdbg" ]
then
    phpdbg -qrr $INFECTION
else
    php $INFECTION
fi

if [[ -v GOLDEN ]]; then
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
