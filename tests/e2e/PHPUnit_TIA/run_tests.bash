#!/usr/bin/env bash

set -eo pipefail
cd "$(dirname "$0")"

if php -r 'exit(version_compare(PHP_VERSION, "8.4.1", "<") ? 0 : 1);'; then
    echo "Skipping PHPUnit TIA: requires PHP 8.4.1 or higher."
    exit 0
fi

export TIA_INFECTION="$(cd ../../.. && pwd)/${1:-bin/infection}"
# Behat must preserve the coverage driver for its PHPUnit subprocesses.
php vendor/bin/behat --xdebug --suite=initial_run
