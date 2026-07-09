#!/usr/bin/env bash

cd "$(dirname "$0")"

set -eo pipefail

# empty directory to emulate PATH without git while preventing the built-in fallback of /bin:/usr/bin
WITHOUT_GIT_PATH="$(mktemp -d)"
readonly WITHOUT_GIT_PATH
trap 'rm -rf "$WITHOUT_GIT_PATH"' EXIT

PHP="$(command -v php)"
readonly PHP

composer install

PATH="$WITHOUT_GIT_PATH" "$PHP" "../../../${1:-bin/infection}" --coverage=infection-coverage

if [ -n "$GOLDEN" ]; then
    cp -v infection.log expected-output.txt
fi

diff -u --ignore-all-space expected-output.txt infection.log
