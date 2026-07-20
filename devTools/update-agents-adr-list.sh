#!/usr/bin/env bash

set -euo pipefail

agents_md=${1:-AGENTS.md}

# Bail out before writing anything: without exactly one of each marker the
# splice below would silently duplicate or truncate the file.
for marker in 'adr-list:start' 'adr-list:end'; do
    if [[ "$(grep -c "<!-- $marker -->" "$agents_md")" != '1' ]]; then
        echo "$0: expected exactly one <!-- $marker --> in $agents_md" >&2
        exit 1
    fi
done

tmp_file=$(mktemp)
trap 'rm -f "$tmp_file"' EXIT

{
    sed '/<!-- adr-list:start -->/q' "$agents_md"

    for file in adr/[0-9][0-9][0-9][0-9]-*.md; do
        if [[ "$file" == 'adr/0000-template.md' ]]; then
            continue
        fi

        title=$(sed -n 's/^# //p; /^# /q' "$file")

        printf -- "- [\`%s\`](%s) - %s\n" "$file" "$file" "$title"
    done

    sed -n '/<!-- adr-list:end -->/,$p' "$agents_md"
} > "$tmp_file"

mv "$tmp_file" "$agents_md"
