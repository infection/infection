#!/usr/bin/env bash

cd "$(dirname "$0")"

if [[ "${1}" != *.phar ]]
then
    # Skipping for non-PHAR as it will conflict with the Mago dependency loaded by Infection itself.
    exit 0
fi

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
