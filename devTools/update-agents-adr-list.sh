#!/usr/bin/env bash

set -euo pipefail

agents_md=${1:-AGENTS.md}

adr_list=$(
    for file in adr/[0-9][0-9][0-9][0-9]-*.md; do
        if [[ "$file" == 'adr/0000-template.md' ]]; then
            continue
        fi

        title=$(sed -n 's/^# //p; /^# /q' "$file")

        printf -- "- [\`%s\`](%s) - %s\n" "$file" "$file" "$title"
    done
)

tmp_file=$(mktemp)
trap 'rm -f "$tmp_file"' EXIT

# Replace everything between the markers with the generated list, and fail if
# the markers are missing or unbalanced so a broken AGENTS.md is never written.
adr_list="$adr_list" awk '
    /^<!-- adr-list:start -->$/ { print; print ENVIRON["adr_list"]; in_block = 1; found_start = 1; next }
    /^<!-- adr-list:end -->$/ { in_block = 0 }
    !in_block
    END { exit !(found_start && !in_block) }
' "$agents_md" > "$tmp_file"

mv "$tmp_file" "$agents_md"
