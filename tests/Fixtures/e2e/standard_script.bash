#!/usr/bin/env bash

php ../../../../bin/infection
if [ $? != 0 ]
then
    echo "error - fault while running infection"
    exit 1
fi

diff expected-output.txt infection-log.txt

if [ $? != 0 ]
then
    echo "error - Difference between files"
    exit 1
fi
