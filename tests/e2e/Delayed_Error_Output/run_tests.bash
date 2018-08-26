#!/usr/bin/env bash

set -e

readonly INFECTION="../../../bin/infection --no-progress"

rm -f infection.log

if [ "$DRIVER" = "phpdbg" ]
then
    phpdbg -qrr $INFECTION > infection.log
else
    php $INFECTION > infection.log
fi

test -x $(which tput) && tput setaf 2 # green
if cat infection.log | grep 'End Of Error'; then
	exit 0;
fi

test -x $(which tput) && tput setaf 1 # red
echo "Infection Killed Process Early"
cat infection.log

exit 1;

