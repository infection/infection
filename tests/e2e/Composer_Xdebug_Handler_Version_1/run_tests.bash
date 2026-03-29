#!/usr/bin/env bash

cd "$(dirname "$0")"

if [ "${1}" = "bin/infection" ]
then
    # skipping for non-PHAR as it will 100% has a conflict with dependencies
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

if [ -n "$GOLDEN" ]; then
    cp -v infection.log expected-output.txt
fi

diff -u --ignore-all-space expected-output.txt infection.log
