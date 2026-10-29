#!/usr/bin/env bash

set -o errexit -o pipefail

readonly INFECTION=../../../${1}

cleanup() {
    rm -rf .git

    if [ -f src/SourceClass.php.original ]; then
        mv src/SourceClass.php.original src/SourceClass.php
    fi
}

trap cleanup EXIT

git init --quiet
git config user.email infection@infection.github.io
git config user.name Infection
git add src
# Keep the fixture commit independent of the user's Git hooks.
git -c core.hooksPath=/dev/null commit --quiet --no-gpg-sign --message baseline

cp src/SourceClass.php src/SourceClass.php.original
sed --in-place=.bak 's/return true;/return true; \/\/ Changed/' src/SourceClass.php
rm src/SourceClass.php.bak

php "$INFECTION" --git-diff-base=HEAD --git-diff-lines

diff --unified=3 --ignore-all-space expected-output.txt var/infection.log

assert_git_diff_failure() {
    local base=$1
    local show_process_details=$2
    local output

    shift 2

    if output=$(php "$INFECTION" --git-diff-base="$base" --git-diff-lines "$@" 2>&1); then
        printf 'Expected a missing Git base to fail.\n%s\n' "$output" >&2
        exit 1
    fi

    printf '%s\n' "$output"

    [[ "$output" == *"Could not compute the Git diff against \"$base\""* ]]

    if [ "$show_process_details" = true ]; then
        [[ "$output" == *"fatal: bad revision '$base'"* ]]
        [[ "$output" == *'Exit Code:'* ]]

        return
    fi

    [[ "$output" == *'Fetch the base reference if it is missing.'* ]]
    [[ "$output" != *'Exit Code:'* ]]
}

assert_git_diff_failure missing-base false
assert_git_diff_failure missing-base false --quiet
assert_git_diff_failure missing-base true --verbose
assert_git_diff_failure missing-base true -vvv
assert_git_diff_failure 'feature/<info>example</info>' false
