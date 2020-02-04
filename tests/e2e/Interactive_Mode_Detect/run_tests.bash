#!/usr/bin/env bash

set -e
cd $(dirname $0)

if [ "$DRIVER" = "phpdbg" ]
then
    INFECTION="phpdbg -qrr ../../../bin/infection"
else
    INFECTION="php ../../../bin/infection"
fi
export INFECTION

test -x $(which tput) && tput setaf 2 # green
if timeout -v -k 10 2 $INFECTION 2>&1 | grep -s 'Infection config generator requires an interactive mode.'; then
	exit 0;
fi

test -x $(which tput) && tput setaf 1 # red
echo "Infection configuration master did not notice output redirection."

exit 1;

