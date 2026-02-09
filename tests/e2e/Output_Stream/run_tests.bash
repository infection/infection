#!/usr/bin/env bash

cd "$(dirname "$0")"

set -e pipefail

readonly INFECTION="../../../bin/infection --no-progress"

rm -f infection.log

if [ "$DRIVER" = "phpdbg" ]
then
    phpdbg -qrr $INFECTION 2> infection.log
else
    php $INFECTION 2> infection.log
fi

if [ -n "$GOLDEN" ]; then
    cp -v infection.log expected-output.txt
fi

diff -u expected-output.txt infection.log
