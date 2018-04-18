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

    diff -u -w expected-output.txt infection-log.txt
}


run "../../../../bin/infection --mutators=FalseValue" "-c php.ini"

