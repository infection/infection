#!/usr/bin/env bash

set -eo pipefail

if ! php -r 'exit(extension_loaded("pcov") && ini_get("pcov.directory") === "" ? 0 : 1);'
then
    exit 0
fi

readonly INFECTION=../../../${1}

php "$INFECTION"

diff --ignore-all-space expected-output.txt var/infection.log
