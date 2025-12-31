#!/usr/bin/env bash

set -e pipefail

readonly INFECTION="../../../bin/infection --ignore-msi-with-no-mutations --min-msi=100"

if [ "$DRIVER" = "phpdbg" ]
then
    phpdbg -qrr $INFECTION
else
    php $INFECTION
fi

diff --ignore-all-space expected-output.txt var/infection.log
