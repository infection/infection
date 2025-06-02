#!/usr/bin/env bash

readonly INFECTION=../../../${1}

set -e pipefail

php $INFECTION --static-analysis-tool=phpstan --no-progress --threads=2

diff -w expected-output.txt infection.log
