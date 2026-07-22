#!/usr/bin/env bash

set -eo pipefail

readonly INFECTION=../../../../${1}
readonly TEST_DIRECTORY=$PWD

composer install --working-dir=server --no-interaction --quiet

cleanup() {
    rm -rf "$TEST_DIRECTORY/.git"

    if [ -f "$TEST_DIRECTORY/server/src/SourceClass.php.original" ]; then
        mv "$TEST_DIRECTORY/server/src/SourceClass.php.original" "$TEST_DIRECTORY/server/src/SourceClass.php"
    fi
}

trap cleanup EXIT

rm -rf .git
git init --quiet
git config user.email infection@infection.github.io
git config user.name Infection
git add .
git commit --quiet -m baseline

cp server/src/SourceClass.php server/src/SourceClass.php.original
sed -i.bak 's/return true;/return true; \/\/ Changed/' server/src/SourceClass.php
rm server/src/SourceClass.php.bak

cd server

php "$INFECTION" --git-diff-base=HEAD --git-diff-filter=M

diff -u --ignore-all-space ../expected-output.txt .var/infection.log
