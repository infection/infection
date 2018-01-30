#!/usr/bin/env bash

#Run test suite with phpunit
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

#Run test suite again, but with phpspec
php ../../../../bin/infection --test-framework=phpspec
if [ $? != 0 ]
then
    echo "error - fault while running infection with phpspec"
    exit 1
fi

diff expected-output.txt infection-log.txt

if [ $? != 0 ]
then
    echo "error - Difference between files with phpspec"
    exit 1
fi
