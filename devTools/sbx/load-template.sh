#!/usr/bin/env bash

set -euo pipefail

. "$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd -P)/config.sh"

if [[ ! -f "${IMAGE_TAR}" ]]; then
    printf 'sbx image tar not found: %s\n' "${IMAGE_TAR}" >&2
    exit 1
fi

if ! command -v sbx >/dev/null 2>&1; then
    cat >&2 <<'EOF'
sbx is required to load the Docker sandbox image template, but it was not found in PATH.

https://docs.docker.com/reference/cli/sbx/template/load/
EOF
    exit 1
fi

set -x
sbx template load "${IMAGE_TAR}"
set +x

printf '%s\n' "${CACHE_KEY}" > "${STAMP}"
