#!/usr/bin/env bash

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd -P)"
KIT_DIR="${SCRIPT_DIR}/kits/php"
DOCKERFILE="${KIT_DIR}/php.dockerfile"
KIT_SPEC="${KIT_DIR}/php.yaml"
TEST_CONFIG="${KIT_DIR}/test.yaml"
PHP_VERSION="${PHP_VERSION:-8.4}"
IMAGE_NAME="${IMAGE_NAME:-infection-sbx-php-${PHP_VERSION}}"
IMAGE_TAG="${IMAGE_TAG:-latest}"
IMAGE_REF="${IMAGE_REF:-${IMAGE_NAME}:${IMAGE_TAG}}"
TEST_IMAGE_REF="${TEST_IMAGE_REF:-${IMAGE_NAME}-test:${IMAGE_TAG}}"
IMAGE_PLATFORM="${IMAGE_PLATFORM:-}"
IMAGE_CACHE_FROM="${IMAGE_CACHE_FROM:-}"
IMAGE_CACHE_TO="${IMAGE_CACHE_TO:-}"

if ! command -v container-structure-test >/dev/null 2>&1; then
    cat >&2 <<'EOF'
container-structure-test is required to test the sandbox kits, but it was not found in PATH.

https://github.com/GoogleContainerTools/container-structure-test
EOF
    exit 1
fi

DOCKER_BUILD_OPTIONS=(--load)
TEST_OPTIONS=()

if [[ -n "${IMAGE_PLATFORM}" ]]; then
    DOCKER_BUILD_OPTIONS+=(--platform="${IMAGE_PLATFORM}")
    TEST_OPTIONS+=(--platform="${IMAGE_PLATFORM}")
fi

if [[ -n "${IMAGE_CACHE_FROM}" ]]; then
    DOCKER_BUILD_OPTIONS+=(--cache-from="${IMAGE_CACHE_FROM}")
fi

if [[ -n "${IMAGE_CACHE_TO}" ]]; then
    DOCKER_BUILD_OPTIONS+=(--cache-to="${IMAGE_CACHE_TO}")
fi

if [[ "${FORCE_REBUILD:-}" == "1" ]]; then
    DOCKER_BUILD_OPTIONS+=(--no-cache)
fi

# Build the full filesystem for executable contract tests. A published mixin
# contains only an overlay and cannot run these commands on its own.
set -x
docker buildx build "${DOCKER_BUILD_OPTIONS[@]}" \
    --build-arg="PHP_VERSION=${PHP_VERSION}" \
    --file="${DOCKERFILE}" \
    --tag="${TEST_IMAGE_REF}" \
    "${KIT_DIR}"
container-structure-test test "${TEST_OPTIONS[@]}" --image="${TEST_IMAGE_REF}" --config="${TEST_CONFIG}"

# The kit frontend emits the overlay and its v3 descriptor, not a workload.
docker buildx build "${DOCKER_BUILD_OPTIONS[@]}" \
    --build-arg="php_version=${PHP_VERSION}" \
    --file="${KIT_SPEC}" \
    --tag="${IMAGE_REF}" \
    "${KIT_DIR}"

# File and standalone CLI checks need no workload filesystem.
for tool in container-structure-test sbx gh-stack codex-otel; do
    tool_kit_dir="${SCRIPT_DIR}/kits/${tool}"
    tool_image_ref="infection-sbx-${tool}:latest"
    docker buildx build "${DOCKER_BUILD_OPTIONS[@]}" \
        --file="${tool_kit_dir}/${tool}.yaml" \
        --tag="${tool_image_ref}" \
        "${tool_kit_dir}"
    container-structure-test test "${TEST_OPTIONS[@]}" \
        --image="${tool_image_ref}" --config="${tool_kit_dir}/test.yaml"
done
