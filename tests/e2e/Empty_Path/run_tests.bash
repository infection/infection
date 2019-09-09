#!/usr/bin/env bash

set -e pipefail

readonly INFECTION="./bootstrap"

if [ "$DRIVER" = "phpdbg" ]
then
    $(which phpdbg) -qrr $INFECTION
else
    $(which php) $INFECTION
fi

diff expected-output.txt infection.log
