#!/usr/bin/env bash

readonly INFECTION=../../../${1}

set -e pipefail

composer install --no-interaction --working-dir=tools

php $INFECTION --static-analysis-tool=phpstan --no-progress --threads=2

diff --ignore-all-space expected-output.txt infection.log
