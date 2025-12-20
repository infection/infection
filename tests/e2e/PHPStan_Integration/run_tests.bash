#!/usr/bin/env bash

readonly INFECTION=../../../${1}

set -e pipefail

php $INFECTION --no-progress --threads=2

diff --ignore-all-space expected-output.txt infection.log
