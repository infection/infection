#!/usr/bin/env bash

cd "$(dirname "$0")"

set -e pipefail

readonly INFECTION="./bootstrap"

if [ "$DRIVER" = "phpdbg" ]
then
    $(which phpdbg) -qrr $INFECTION
else
    $(which php) $INFECTION
fi

if [ -n "$GOLDEN" ]; then
    cp -v infection.log expected-output.txt
fi

diff -u expected-output.txt infection.log
