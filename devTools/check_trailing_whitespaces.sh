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

set -Eeuo pipefail

files_with_trailing_whitespaces=$(
    find . \
        -type f \
        -not -name "*.cache" \
        -not -name "*.log" \
        -not -path "./.composer/*" \
        -not -path "./build/*" \
        -not -path "./.git/*" \
        -not -path "./vendor/*" \
        -not -path "./tests/e2e/*" \
        -not -path "./tests/phpunit/Fixtures/Files/phpunit/format-whitespace/original-phpunit.xml" \
        -not -path "./tests/phpunit/StringNormalizerTest.php" \
        -not -path "./tests/phpunit/StrTest.php" \
        -exec grep -EIHn "\\s$" {} \;
)

if [[ "$files_with_trailing_whitespaces" ]]
then
    printf '\033[97;41mTrailing whitespaces detected:\033[0m\n';
    e=$(printf '\033');
    echo "${files_with_trailing_whitespaces}" \
      | sed -E "s/^\\.\\/([^:]+):([0-9]+):(.*[^\\t ])?([\\t ]+)$/${e}[0;31m - in ${e}[0;33m\\1${e}[0;31m at line ${e}[0;33m\\2\\n   ${e}[0;31m>${e}[0m \\3${e}[41;1m\\4${e}[0m/";

    exit 1;
fi

printf '\033[0;32mNo trailing whitespaces detected.\033[0m\n';
