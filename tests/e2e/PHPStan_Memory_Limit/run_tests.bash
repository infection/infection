#!/usr/bin/env bash

cd "$(dirname "$0")"

readonly INFECTION=../../../${1}

set -eo pipefail

rm -rf var/*

php -d memory_limit=-1 "$INFECTION" --no-progress --threads=1

if [ -n "$GOLDEN" ]; then
    cp -v var/infection.log expected-output.txt
fi;

diff -u --ignore-all-space expected-output.txt var/infection.log
