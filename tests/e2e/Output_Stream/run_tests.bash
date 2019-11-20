#!/usr/bin/env bash

set -e pipefail

readonly INFECTION="../../../bin/infection --no-progress"

rm -f infection.log

if [ "$DRIVER" = "phpdbg" ]
then
    phpdbg -qrr $INFECTION 2> infection.log
else
    php $INFECTION 2> infection.log
fi

diff expected-output.txt infection.log
