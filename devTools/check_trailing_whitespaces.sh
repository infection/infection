#!/usr/bin/env bash

#
# This file is part of PHP CS Fixer (https://github.com/FriendsOfPHP/PHP-CS-Fixer).
#
# Copyright (c) 2012-2019 Fabien Potencier
#                         Dariusz Rumiński
#
# Permission is hereby granted, free of charge, to any person obtaining a copy of this software and
# associated documentation files (the "Software"), to deal in the Software without restriction,
# including without limitation the rights to use, copy, modify, merge, publish, distribute,
# sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is
# furnished to do so, subject to the following conditions:
#
# The above copyright notice and this permission notice shall be included in all copies or
# substantial portions of the Software.
#
# THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT
# NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
# NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM,
# DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
# OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

set -eu

stderr_file=$(mktemp)
trap 'rm -f "$stderr_file"' EXIT

files_with_trailing_spaces=$(
    git grep -In "\\s$" \
        ':!tests/autoloaded/*' \
        ':!tests/e2e/*' \
        ':!tests/phpunit/Framework/StrTest.php' \
        ':!tests/phpunit/TestFramework/PhpUnit/Config/Builder/Fixtures/format-whitespace/original-phpunit.xml' \
        ':!tests/benchmark/Tracing/benchmark-source' \
        ':!tests/benchmark/MutationGenerator/sources.tar.gz' \
        2>"$stderr_file" \
    | sort -fh
) || true

if [[ -s "$stderr_file" ]]; then
    cat "$stderr_file" >&2
    exit 1
fi

if [ "$files_with_trailing_spaces" ]
then
    printf '\033[97;41mTrailing whitespaces detected:\033[0m\n'
    e=$(printf '\033')
    echo "${files_with_trailing_spaces}" | sed -E "s/^([^:]+):([0-9]+):(.*[^\\t ])?([\\t ]+)$/${e}[0;31m - in ${e}[0;33m\\1${e}[0;31m at line ${e}[0;33m\\2\\n   ${e}[0;31m>${e}[0m \\3${e}[41;1m\\4${e}[0m/"

    exit 3
fi

printf '\033[0;32mNo trailing whitespaces detected.\033[0m\n'
