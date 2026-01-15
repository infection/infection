#!/usr/bin/env bash

cd "$(dirname "$0")"

readonly INFECTION=../../../bin/infection

set -e pipefail

if [ "$DRIVER" = "phpdbg" ]
then
    phpdbg -d variables_order=EGPCS -qrr $INFECTION
else
    php -d variables_order=EGPCS $INFECTION
fi

if [ -n "$GOLDEN" ]; then
    cp -v infection.log expected-output.txt
fi

diff -u --ignore-all-space expected-output.txt infection.log
