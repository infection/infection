#!/usr/bin/env bash

cd "$(dirname "$0")"

readonly INFECTION=../../../${1}

set -eo pipefail

mkdir -p var

php $INFECTION --no-progress --threads=2 --debug --log-verbosity=all --logger-text=var/combined-output.log

if [ -n "$GOLDEN" ]; then
    cp -v infection.log expected-output.txt
fi;

diff -u --ignore-all-space expected-output.txt infection.log

php verify-text-report.php var/combined-output.log
