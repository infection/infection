#!/usr/bin/env bash

set -o pipefail

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
    php $INFECTION
fi

diff expected-output.txt infection-log.txt
