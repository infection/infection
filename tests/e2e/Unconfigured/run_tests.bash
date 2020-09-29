#!/usr/bin/env bash

set -e

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

test -x $(which tput) && tput setaf 2 # green
if run "../../../bin/infection --no-interaction" "" < /dev/null 2>&1 | grep -sE '(Aborted.|Infection config generator requires an interactive mode.)'; then
	exit 0;
fi

test -x $(which tput) && tput setaf 1 # red
echo "Infection configuration master did not start."
rm -f infection.json.dist
exit 1;

