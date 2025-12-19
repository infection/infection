#!/usr/bin/env bash

readonly INFECTION=../../../${1}

set -e pipefail

if [ "$DRIVER" = "phpdbg" ]
then
    phpdbg -qrr $INFECTION
else
    php $INFECTION
fi

if [ -f "var/infection.log" ]; then
    diff --ignore-all-space expected-output.txt var/infection.log
else
    diff --ignore-all-space expected-output.txt infection.log
fi
