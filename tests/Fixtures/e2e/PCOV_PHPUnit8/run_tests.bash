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
    exit 0
fi

if php -r "exit(version_compare(PHP_VERSION, '7.3.0'));"
then
    exit 0
fi

tputx bold
echo "Checking for PCOV..."
tputx sgr0


if ! php --ri pcov
then
    tput setaf 1 # red
    echo "PCOV not detected"
    exit 0
fi

set -e pipefail

run "../../../../bin/infection"

diff -w expected-output.txt infection.log

