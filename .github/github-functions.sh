#!/usr/bin/env bash

set -Eeuo pipefail

function get_infection_pr_flags() {
    local flags="";
    local changed_files;

    git fetch;

    changed_files=$(git diff origin/"${GITHUB_BASE_REF:-master}" --diff-filter=A --name-only | grep src/ | paste -sd "," -);

    if [ -n "$changed_files" ]; then
        # Set those flags only if there is any changed files detected
        flags="--filter=${changed_files} --ignore-msi-with-no-mutations --only-covered ${flags}";
    fi

    echo "$flags";
}
