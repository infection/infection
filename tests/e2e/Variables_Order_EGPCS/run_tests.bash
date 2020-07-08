#!/usr/bin/env bash

readonly INFECTION=../../../bin/infection

set -e pipefail

if [ "$DRIVER" = "phpdbg" ]
then
    phpdbg -d variables_order=EGPCS -qrr $INFECTION
else
    php -d variables_order=EGPCS $INFECTION
fi

if [[ -v GOLDEN ]]; then
   cp -v infection.log expected-output.txt
fi

diff -w expected-output.txt infection.log
