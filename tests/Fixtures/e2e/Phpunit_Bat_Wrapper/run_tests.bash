#!/usr/bin/env bash

set -e

tputx () {
    test -x $(which tput) && tput "$@"
}

run () {
    local INFECTION=${1}
    local PHPARGS=${2}

    if [ "$DRIVER" = "phpdbg" ]
    then
        phpdbg $PHPARGS -qrr $INFECTION
    else
        php $PHPARGS $INFECTION
    fi
}

cd $(dirname "$0")

if [ "$DRIVER" = "phpdbg" ]
then
    tputx bold
    echo "Will be using phpdbg"
    tputx sgr0
fi

if [ -e vendor/bin/phpunit ]
then
    tputx bold
    echo "Switching to our wrapper..."
    mv -v vendor/bin/phpunit vendor/bin/phpunit-actual
    tputx sgr0
fi

rm -f phpunit.bat.canary

PATH=.:$PATH run "../../../../bin/infection --quiet" || true

test -f phpunit.bat.canary && rm phpunit.bat.canary

tputx bold
echo "Success!"
tputx sgr0
