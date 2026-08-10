#!/usr/bin/env bash

cd "$(dirname "$0")"

if [ "$(php -r 'echo version_compare(PHP_VERSION, "8.3.0", "<");')" = "1" ]; then
    echo "Skipping test: it needs PHP 8.3.0 or higher (found $(php -r 'echo PHP_VERSION;'))"
    exit 0
fi

readonly INFECTION=../../../${1}
readonly DEFAULT_PHP_INI_SCAN_DIR=$(php -r 'echo PHP_CONFIG_FILE_SCAN_DIR;')

set -eo pipefail

set +e
output=$(PHP_INI_SCAN_DIR="$DEFAULT_PHP_INI_SCAN_DIR:$PWD" php "$INFECTION" --no-ansi --no-progress 2>&1)
exit_code=$?
set -e

if [ "$exit_code" -ne 1 ]; then
    echo "Expected Infection to exit with code 1, got $exit_code"
    echo "$output"
    exit 1
fi

compact_output=$(echo "$output" | tr -d '[:space:]')

while IFS= read -r expected; do
    compact_expected=$(echo "$expected" | tr -d '[:space:]')

    if [[ "$compact_output" != *"$compact_expected"* ]]; then
        echo "Expected Infection output to contain: $expected"
        echo "$output"
        exit 1
    fi
done < expected-output.txt
