#!/usr/bin/env bash
if [ "$PHPDBG" = "1" ]
then
    phpdbg -qrr ../../../../bin/infection --test-framework=codeception
else
    php ../../../../bin/infection --test-framework=codeception
fi

if [ $? -ne 0 ]
then
    echo "error - fault while running infection"
    exit 1
fi

diff expected-output.txt infection-log.txt

if [ $? -ne 0 ]
then
    echo "error - Difference between files"
    exit 1
fi
