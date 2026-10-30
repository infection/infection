#!/usr/bin/env bash

set -eo pipefail

readonly TEST_DIRECTORY=$PWD
INFECTION=$(realpath "../../../${1}")
readonly INFECTION

composer install --working-dir=server --no-interaction --quiet

cleanup() {
    rm -rf "$TEST_DIRECTORY/.git"

    if [ -f "$TEST_DIRECTORY/server/src/SourceClass.php.original" ]; then
        mv "$TEST_DIRECTORY/server/src/SourceClass.php.original" "$TEST_DIRECTORY/server/src/SourceClass.php"
    fi

    if [ -f "$TEST_DIRECTORY/shared/SharedClass.php.original" ]; then
        mv "$TEST_DIRECTORY/shared/SharedClass.php.original" "$TEST_DIRECTORY/shared/SharedClass.php"
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
cp shared/SharedClass.php shared/SharedClass.php.original
sed -i.bak 's/return true;/return true; \/\/ Changed/' server/src/SourceClass.php
sed -i.bak 's/return true;/return true; \/\/ Changed/' shared/SharedClass.php
rm server/src/SourceClass.php.bak
rm shared/SharedClass.php.bak

run_infection() {
    local working_directory=$1
    local configuration=$2

    (
        cd "$working_directory"
        php "$INFECTION" --configuration="$configuration" --git-diff-base=HEAD --git-diff-filter=M
    )

    diff --unified --ignore-all-space expected-output.txt server/.var/infection.log
}

run_infection server infection.json5
run_infection . server/infection.json5
run_infection frontend ../server/infection.json5
