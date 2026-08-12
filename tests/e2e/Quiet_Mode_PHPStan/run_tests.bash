#!/usr/bin/env bash

cd "$(dirname "$0")"

readonly INFECTION=../../../${1}

set -eo pipefail

run_test() {
    SHELL_VERBOSITY="$1" php $INFECTION --no-progress

    diff --unified --ignore-all-space expected-output.txt var/infection.log
}

run_test 0
run_test -1
