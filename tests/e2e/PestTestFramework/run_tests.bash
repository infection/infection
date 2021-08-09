#!/usr/bin/env bash

readonly INFECTION="../../../bin/infection --test-framework=pest --debug"

set -e pipefail

if [ "$DRIVER" = "phpdbg" ]
then
    phpdbg -qrr $INFECTION
else
    php $INFECTION
fi

diff -w expected-output.txt infection.log
