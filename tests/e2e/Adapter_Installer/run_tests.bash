#!/usr/bin/env bash

if test ! -f "$(which expect)"; then
    test -x $(which tput) && tput setaf 1 # red
    echo "Please install expect; it is readily available from apt and brew"
    exit 1;
fi

cd $(dirname $0)

set -e

if [ "$DRIVER" = "phpdbg" ]
then
    INFECTION="phpdbg -qrr ../../../bin/infection --test-framework=codeception"
else
    INFECTION="php ../../../bin/infection --test-framework=codeception"
fi
export INFECTION

./check_auto_install.expect

