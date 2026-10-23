#!/usr/bin/env bash

set -euo pipefail

readonly INFECTION="../../../${1:-bin/infection}"

run_selection() {
    local selector="$1"
    local expected_count="$2"

    if [ "${DRIVER:-}" = "phpdbg" ]
    then
        phpdbg -qrr "$INFECTION" run "$selector" --no-progress --no-interaction
    else
        php "$INFECTION" run "$selector" --no-progress --no-interaction
    fi

    diff -u <(sed "s/: 1$/: $expected_count/" expected-output.txt) var/infection.log
}

run_selection 'Calculator::add' 1
run_selection 'Differ' 1
run_selection 'Differ/' 2
run_selection 'Differ\' 1
run_selection 'SourceSymbolSelection\Differ\' 2
run_selection '\Differ' 1
run_selection 'src/Differ' 2
