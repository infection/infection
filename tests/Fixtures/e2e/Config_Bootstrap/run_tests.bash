#!/usr/bin/env bash

readonly INFECTION=../../../../bin/infection

rm -rf infection-file.txt

set -e pipefail

if [ "$PHPDBG" = "1" ]
then
    phpdbg -qrr $INFECTION
else
    php $INFECTION
fi

diff -w expected-output.txt infection-log.txt
diff -w expected-file.txt infection-file.txt
