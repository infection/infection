#!/usr/bin/env bash

# turbocov is an opt-in coverage driver: it cannot be loaded alongside Xdebug
# or PCOV, so this scenario only runs when turbocov is the loaded extension.

set -e

cd "$(dirname "$0")"

if ! php --ri turbocov > /dev/null 2>&1
then
    echo "Skipping test: the turbocov extension is not loaded"
    exit 0
fi

readonly INFECTION=../../../${1}

set -eo pipefail

# Force an empty `turbocov.collect_paths` so that Infection has to provide it,
# whatever the local PHP configuration is.
php -d turbocov.collect_paths= $INFECTION

if [ -n "$GOLDEN" ]; then
    cp -v var/infection.log expected-output.txt
fi

diff -u --ignore-all-space expected-output.txt var/infection.log
