#!/usr/bin/env bash

readonly INFECTION=../../../${1}

set -e pipefail

php $INFECTION
