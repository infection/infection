#!/usr/bin/env bash

set -euo pipefail

readonly INFECTION="../../../${1:-bin/infection}"

output="$(php "$INFECTION" ../../../tests/phpunit/Differ Differ::diff UnifiedDiffOutputBuilder --with-uncovered --dry-run --no-progress --no-interaction --show-mutations=max 2>&1)"

grep --fixed-strings 'src/Differ/Differ.php' <<< "$output"
grep --fixed-strings 'src/Differ/UnifiedDiffOutputBuilder.php' <<< "$output"

if grep --fixed-strings 'src/Differ/ChangedLinesRange.php' <<< "$output"
then
    echo 'A non-selected source symbol was mutated.' >&2

    exit 1
fi

php "$INFECTION" ../../../tests/phpunit/Differ Differ::diff Differ::missing --with-uncovered --dry-run --no-progress --no-interaction
