#!/usr/bin/env bash

cd "$(dirname "$0")"

if [ "$(php -r 'echo version_compare(PHP_VERSION, "8.3.0", "<");')" = "1" ]; then
    echo "Skipping test: it needs PHP 8.3.0 or higher (found $(php -r 'echo PHP_VERSION;'))"
    exit 0
fi

readonly INFECTION=../../../${1}
readonly DEFAULT_PHP_INI_SCAN_DIR=$(php -r 'echo PHP_CONFIG_FILE_SCAN_DIR;')

set -eo pipefail

PHP_INI_SCAN_DIR="$DEFAULT_PHP_INI_SCAN_DIR:$PWD" php "$INFECTION"

diff --ignore-all-space expected-output.txt var/infection.log
