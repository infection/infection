#!/usr/bin/env bash

set -e

tputx () {
	test -x $(which tput) && tput "$@"
}

run () {
    local INFECTION=${1}
    local PHPARGS=${2}

    if [ "$DRIVER" = "phpdbg" ]
    then
        phpdbg $PHPARGS -qrr $INFECTION
    else
        php $PHPARGS $INFECTION
    fi
}

cd $(dirname "$0")

if [ "$DRIVER" = "phpdbg" ]
then
    tputx bold
    echo "Will be using phpdbg"
    tputx sgr0
fi

tputx bold
echo "Initial test run outside Infection must be successful"
tputx sgr0

if [ "$DRIVER" = "phpdbg" ]
then
    PATH=$PATH:bin phpdbg -qrr vendor/bin/phpunit --coverage-xml=coverage/coverage-xml --log-junit=coverage/phpunit.junit.xml
else
    PATH=$PATH:bin php vendor/bin/phpunit --coverage-xml=coverage/coverage-xml --log-junit=coverage/phpunit.junit.xml
fi

test -f coverage/phpunit.junit.xml
test -f coverage/coverage-xml/RunShellScript.php.xml
test -f coverage/coverage-xml/index.xml

tputx bold
echo "PHPUnit finished all right"
echo "Pre-generated coverage..."
tputx sgr0

PATH=$PATH:bin run "../../../../bin/infection --coverage=coverage --quiet"
diff -uw expected-output.txt infection.log

tputx bold
echo "Internal coverage..."
tputx sgr0

PATH=$PATH:bin run "../../../../bin/infection --quiet"

diff -w expected-output.txt infection.log

rm -vfr coverage
