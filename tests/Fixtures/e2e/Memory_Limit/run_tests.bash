#!/usr/bin/env bash

if [ "$PHPDBG" = "1" ]
then
    # Memory limit cannot be enforced from our custom php.ini
    # under PHPDBG, hence this test shows nothing under PHPDBG
    exit 0
fi

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


run "../../../../bin/infection --mutators=FalseValue" "-d memory_limit=-1"

