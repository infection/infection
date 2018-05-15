#!/usr/bin/env bash

set -e

tputx () {
    test -x $(which tput) && tput "$@"
}

run () {
    local INFECTION=${1}
    local PHPARGS=${2}

    if [ "$PHPDBG" = "1" ]
    then
        phpdbg $PHPARGS -qrr $INFECTION
    else
        php $PHPARGS $INFECTION
    fi
}

cd $(dirname "$0")

if [ "$PHPDBG" = "1" ]
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

run "../../../../bin/infection --quiet"
diff -w expected-output.txt infection-log.txt

test -f phpunit.bat.canary && rm phpunit.bat.canary

tputx bold
echo "Success!"
tputx sgr0
