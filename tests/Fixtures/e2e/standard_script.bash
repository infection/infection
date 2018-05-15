#!/usr/bin/env bash

readonly INFECTION=../../../../${1}

set -e pipefail

if [ "$PHPDBG" = "1" ]
then
    phpdbg -qrr $INFECTION
else
    php $INFECTION
fi

diff -w expected-output.txt infection-log.txt
