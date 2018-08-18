#!/usr/bin/env bash

set -e pipefail

readonly INFECTION="../../../../bin/infection --ignore-msi-with-no-mutations --filter=notExistentFile.php --min-msi=100"

if [ "$PHPDBG" = "1" ]
then
    phpdbg -qrr $INFECTION
else
    php $INFECTION
fi

diff expected-output.txt infection.log