#!/usr/bin/env bash

cd "$(dirname "$0")"

readonly INFECTION="../../../${1} --with-uncovered"

set -e pipefail

if [ "$DRIVER" = "phpdbg" ]
then
    phpdbg -qrr $INFECTION
else
    php $INFECTION
fi

if [ -n "$GOLDEN" ]; then
    cp -v infection.log expected-output.txt
fi

diff -u --ignore-all-space expected-output.txt infection.log
