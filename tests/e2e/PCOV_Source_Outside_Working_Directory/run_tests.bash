#!/usr/bin/env bash

set -eo pipefail

INFECTION=$(realpath "../../../${1}")
readonly INFECTION

composer install --working-dir=server --no-interaction --quiet

(
    cd server
    php "$INFECTION"
)

diff --ignore-all-space expected-output.txt server/.var/infection.log
