#!/usr/bin/env bash

cd "$(dirname "$0")"

readonly INFECTION=../../../${1}

set -e pipefail

if [ "$DRIVER" = "phpdbg" ]
then
    phpdbg -qrr $INFECTION --no-progress --threads=2
else
    php -d memory_limit=-1 $INFECTION --no-progress --threads=2
fi

if [ -n "$GOLDEN" ]; then
    cp -v infection.log expected-output.txt
fi;

diff -u --ignore-all-space expected-output.txt infection.log
