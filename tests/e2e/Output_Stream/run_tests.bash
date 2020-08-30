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

if [[ -v GOLDEN ]]; then
   cp -v infection.log expected-output.txt
fi

diff -u expected-output.txt infection.log
