#!/usr/bin/env bash

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

diff -w expected-output.txt infection.log
