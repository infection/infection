#!/usr/bin/env bash

set -o pipefail

readonly INFECTION="../../../../bin/infection --coverage=infection-coverage"

if [ "$PHPDBG" = "1" ]
then
    phpdbg -qrr $INFECTION
else
    php $INFECTION
fi

diff expected-output.txt infection-log.txt

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

#if [ "$PHPDBG" = "1" ]
#then
#    phpdbg -qrr $INFECTION  --coverage="infection-coverage" --test-framework=phpspec
#else
#    php $INFECTION
#fi
#
#diff expected-output.txt infection-log.txt
