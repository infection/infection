#!/usr/bin/env bash

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd -P)"

DOCKER_BUILD_COMMAND=(
    docker build
)

if [[ -n "${IMAGE_PLATFORM:-}" ]]; then
    # This is relevant for the CI when the target platform may be different
    # than the platform of the machine.
    DOCKER_BUILD_COMMAND=(
        docker buildx build
            --load
            --platform="${IMAGE_PLATFORM}"
    )
fi

DOCKER_BUILD_CACHE_OPTIONS=()

if [[ -n "${IMAGE_CACHE_FROM:-}" ]]; then
    DOCKER_BUILD_CACHE_OPTIONS+=(
        --cache-from="${IMAGE_CACHE_FROM}"
    )
fi

if [[ -n "${IMAGE_CACHE_TO:-}" ]]; then
    DOCKER_BUILD_CACHE_OPTIONS+=(
        --cache-to="${IMAGE_CACHE_TO}"
    )
fi

set -x
"${DOCKER_BUILD_COMMAND[@]}" \
    "${DOCKER_BUILD_CACHE_OPTIONS[@]}" \
    --build-arg="PHP_VERSION=${PHP_VERSION}" \
    --file="${SCRIPT_DIR}/Dockerfile" \
    --tag="${IMAGE_REF}" \
    "${SCRIPT_DIR}"
set +x

printf '%s\n' "${IMAGE_REF}"

if ! command -v container-structure-test >/dev/null 2>&1; then
    cat >&2 <<'EOF'
container-structure-test is required to verify the Docker sandbox image, but it was not found in PATH.

https://github.com/GoogleContainerTools/container-structure-test
EOF
    exit 1
fi

set -x
container-structure-test test --image=${IMAGE_REF} --config=${SCRIPT_DIR}/test.yaml
set +x
