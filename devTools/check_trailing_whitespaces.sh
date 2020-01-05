#!/usr/bin/env bash

set -eu

files_with_trailing_whitespaces=$(
    find . \
        -type f \
        -not -path "./.git/*" \
        -not -path "./vendor/*" \
        -exec grep -EIHn "\\s$" {} \;
)

if [[ "$files_with_trailing_whitespaces" ]]
then
    printf '\033[97;41mTrailing whitespaces detected:\033[0m\n'
    e=$(printf '\033')
    echo "${files_with_trailing_whitespaces}" | sed -E "s/^\\.\\/([^:]+):([0-9]+):(.*[^\\t ])?([\\t ]+)$/${e}[0;31m - in ${e}[0;33m\\1${e}[0;31m at line ${e}[0;33m\\2\\n   ${e}[0;31m>${e}[0m \\3${e}[41;1m\\4${e}[0m/"
    exit 1
fi

printf '\033[0;32mNo trailing whitespaces detected.\033[0m\n'
