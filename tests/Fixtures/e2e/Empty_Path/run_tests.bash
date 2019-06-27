#!/usr/bin/env bash

set -e pipefail

cd $(dirname "$0")

readonly INFECTION="./../../../../bin/infection"
readonly PHP=$(command -v php)

export PATH=

if [ "$PHPDBG" = "1" ]
then
    $(which phpdbg) -qrr $INFECTION
else
    $PHP $INFECTION
fi

diff expected-output.txt infection.log