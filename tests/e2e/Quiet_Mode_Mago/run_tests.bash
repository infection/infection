#!/usr/bin/env bash

cd "$(dirname "$0")"

if [[ "${1}" != *.phar ]]
then
    # Skipping for non-PHAR as it will conflict with the Mago dependency loaded by Infection itself.
    exit 0
fi

readonly INFECTION=../../../${1}

set -eo pipefail

run_test() {
    SHELL_VERBOSITY="$1" php $INFECTION

    diff --unified --ignore-all-space expected-output.txt infection.log
}

run_test 0
run_test -1
