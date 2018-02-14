#!/usr/bin/env bash

readonly INFECTION=../../../../bin/infection

set -e pipefail

if [ "$PHPDBG" = "1" ]
then
    phpdbg -qrr $INFECTION
else
    php $INFECTION
fi

diff expected-output.txt infection-log.txt
