#!/usr/bin/env bash

set -euo pipefail

test "$(container-structure-test version)" = 1.22.1
