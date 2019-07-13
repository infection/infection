#!/usr/bin/env bash

readonly INFECTION=../../../../bin/infection

set -e pipefail

if [ "$DRIVER" = "phpdbg" ]
then
    phpdbg -d variables_order=EGPCS -qrr $INFECTION
else
    php -d variables_order=EGPCS $INFECTION
fi

diff -w expected-output.txt infection.log
