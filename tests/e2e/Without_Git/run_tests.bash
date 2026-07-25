#!/usr/bin/env bash

cd "$(dirname "$0")"

set -eo pipefail

# an absolute path to a directory to emulate PATH without git while
# preventing the built-in fallback of /bin:/usr/bin
readonly WITHOUT_GIT_PATH="$PWD/bin"

PHP="$(command -v php)"
readonly PHP

composer install

if PATH="$WITHOUT_GIT_PATH" command -v git; then
    echo "git is still reachable; improve this test" >&2
    exit 1
fi

PATH="$WITHOUT_GIT_PATH" "$PHP" "../../../${1:-bin/infection}"

if [ -n "$GOLDEN" ]; then
    cp -v infection.log expected-output.txt
fi

diff -u --ignore-all-space expected-output.txt infection.log
