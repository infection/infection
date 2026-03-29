#!/usr/bin/env bash

cd "$(dirname "$0")"

set -e pipefail

readonly INFECTION="../../../bin/infection --ignore-msi-with-no-mutations --min-msi=100"

if [ "$DRIVER" = "phpdbg" ]
then
    phpdbg -qrr $INFECTION
else
    php $INFECTION
fi

if [ -n "$GOLDEN" ]; then
    cp -v var/infection.log expected-output.txt
fi

diff -u --ignore-all-space expected-output.txt var/infection.log
