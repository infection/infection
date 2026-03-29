#!/usr/bin/env bash

cd "$(dirname "$0")"

readonly INFECTION=../../../bin/infection

rm -rf infection-file.txt

set -e pipefail

if [ "$DRIVER" = "phpdbg" ]
then
    phpdbg -qrr $INFECTION
else
    php $INFECTION
fi

if [ -n "$GOLDEN" ]; then
    cp -v infection.log expected-output.txt
    cp -v infection-file.txt expected-file.txt
fi

diff -u expected-output.txt infection.log
diff -u expected-file.txt infection-file.txt
