#!/usr/bin/env bash

readonly INFECTION=../../../${1}

set -e pipefail

if [ "$DRIVER" = "phpdbg" ]
then
    phpdbg -qrr $INFECTION
else
    php $INFECTION
fi

diff --ignore-all-space expected-output.txt infection.log
