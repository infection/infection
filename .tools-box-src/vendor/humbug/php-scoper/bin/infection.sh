#!/usr/bin/env bash

#
# Utility shell to execute the (non scoped) infection more easily. Indeed the
# the infection location makes it awkward to use.
#

set -Eeuo pipefail

cd fixtures/set020-infection \
    && php vendor/infection/infection/bin/infection \
		--only-covered \
		$@
