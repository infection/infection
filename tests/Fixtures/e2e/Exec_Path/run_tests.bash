#!/usr/bin/env bash

set -e

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

PATH=$PATH:bin php vendor/bin/phpunit

PATH=$PATH:bin run "../../../../bin/infection"

diff -w expected-output.txt infection-log.txt
