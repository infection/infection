#!/usr/bin/env bash

set -e pipefail

readonly INFECTION=../../../../bin/infection

if [ "$PHPDBG" = "1" ]
then
    phpdbg -qrr $INFECTION
else
    php $INFECTION
fi

diff expected-output.txt infection-log.txt



if [ "$PHPDBG" = "1" ]
then
    phpdbg -qrr $INFECTION --test-framework=phpspec
else
    php $INFECTION --test-framework=phpspec
fi

diff expected-output.txt infection-log.txt
