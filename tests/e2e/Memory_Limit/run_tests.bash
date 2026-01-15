#!/usr/bin/env bash

cd "$(dirname "$0")"

if [ "$DRIVER" = "phpdbg" ]
then
    # Memory limit cannot be enforced from our custom php.ini
    # under PHPDBG, hence this test shows nothing under PHPDBG
    exit 0
fi

set -e

run () {
    local INFECTION=${1}
    local PHPARGS=${2}

    if [ "$DRIVER" = "phpdbg" ]
    then
        phpdbg $PHPARGS -qrr $INFECTION
    else
        php $PHPARGS $INFECTION
    fi

    if [ -n "$GOLDEN" ]; then
        cp -v infection.log expected-output.txt
    fi

    diff -u --ignore-all-space expected-output.txt infection.log
}


run "../../../bin/infection --mutators=FalseValue" "-d memory_limit=-1"

