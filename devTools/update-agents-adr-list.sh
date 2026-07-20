#!/usr/bin/env bash

set -euo pipefail

agents_md=${1:-AGENTS.md}

tmp_file=$(mktemp)
adr_list_file=$(mktemp)

cleanup() {
    rm -f "$tmp_file" "$adr_list_file"
}

trap cleanup EXIT

{
    printf '<!-- adr-list:start -->\n'

    for file in adr/[0-9][0-9][0-9][0-9]-*.md; do
        if [[ "$file" == 'adr/0000-template.md' ]]; then
            continue
        fi

        title=$(sed -n 's/^# //p; /^# /q' "$file")

        printf -- "- [\`%s\`](%s) - %s\n" "$file" "$file" "$title"
    done

    printf '<!-- adr-list:end -->\n'
} > "$adr_list_file"

awk \
    -v adr_list_file="$adr_list_file" \
    'BEGIN {
        while ((getline line < adr_list_file) > 0) {
            generated = generated line "\n";
        }
    }
    /^<!-- adr-list:start -->$/ {
        printf "%s", generated;
        in_block = 1;
        found_start = 1;
        next;
    }
    /^<!-- adr-list:end -->$/ && in_block {
        in_block = 0;
        found_end = 1;
        next;
    }
    !in_block { print }
    END {
        if (!found_start || !found_end || in_block) {
            exit 1;
        }
    }' \
    "$agents_md" > "$tmp_file"

mv "$tmp_file" "$agents_md"
