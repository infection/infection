#!/usr/bin/env bash

cd "$(dirname "$0")"

if [ $(php -r 'echo version_compare(PHP_VERSION, "8.4.1", "<");') ]; then
    echo "Skipping test it needs PHP 8.4.1 or higher (found $(php -r 'echo PHP_VERSION;'))"
    exit 0
fi

readonly INFECTION=../../../${1}

set -eo pipefail

run_test() {
    SHELL_VERBOSITY="$1" php $INFECTION

    diff -u --ignore-all-space expected-output.txt var/infection.log
}

run_test 0
run_test -1
