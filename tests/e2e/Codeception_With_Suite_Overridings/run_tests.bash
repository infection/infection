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

tputx bold
echo "Checking for sqlite3 extension..."
tputx sgr0


if ! php --ri sqlite3
then
    tput setaf 1 # red
    echo "sqlite3 not detected"
    exit 0
fi

set -e pipefail

run "../../../bin/infection"

diff -w expected-output.txt infection.log

