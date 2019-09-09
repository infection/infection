#!/usr/bin/env bash

set -e pipefail

readonly INFECTION="../../../bin/infection"

if [ "$DRIVER" = "pcov" ]
then
    # pcov does not work with phpspec, skipping...
    exit 0
fi

if [ "$DRIVER" = "phpdbg" ]
then
    phpdbg -qrr $INFECTION
else
    php $INFECTION
fi

diff -w expected-output.txt infection.log
