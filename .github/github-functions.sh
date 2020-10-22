#!/usr/bin/env bash

set -Eeuo pipefail

function get_infection_pr_flags() {
    local flags="";
    local changed_files;

    PR_NUMBER=$(echo $GITHUB_REF | awk 'BEGIN { FS = "/" } ; { print $3 }')

    if ! [[ "${PR_NUMBER}" == "" ]]; then
        git remote set-branches --add origin "$GITHUB_HEAD_REF";
        git fetch;

        changed_files=$(git diff master --diff-filter=A --name-only | grep src/ | paste -sd "," -);

        if [ -n "$changed_files" ]; then
            # Set those flags only if there is any changed files detected
            flags="--filter=${changed_files} --ignore-msi-with-no-mutations --only-covered ${flags}";
        fi
    fi

    echo "$flags";
}
