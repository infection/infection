#!/usr/bin/env bash

set -e pipefail

if [ "$DRIVER" = "pcov" ]
then
    # pcov does not work with phpspec, skipping...
    exit 0
fi

readonly INFECTION=../../../../bin/infection

if [ "$DRIVER" = "phpdbg" ]
then
    phpdbg -qrr $INFECTION
else
    php $INFECTION
fi

diff expected-output.txt infection.log



if [ "$DRIVER" = "phpdbg" ]
then
    phpdbg -qrr $INFECTION --test-framework=phpspec
else
    php $INFECTION --test-framework=phpspec
fi

diff expected-output.txt infection.log
