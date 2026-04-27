#!/usr/bin/env bash

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd -P)"
ROOT_DIR="$(cd "${SCRIPT_DIR}/../.." && pwd -P)"
DOCKERFILE="${SCRIPT_DIR}/Dockerfile"
TEST_CONFIG="${SCRIPT_DIR}/test.yaml"
PHP_VERSION="${PHP_VERSION:-8.4}"
IMAGE_NAME="${IMAGE_NAME:-infection-sbx-php-${PHP_VERSION}}"
IMAGE_TAG="${IMAGE_TAG:-latest}"
IMAGE_REF="${IMAGE_REF:-${IMAGE_NAME}:${IMAGE_TAG}}"
IMAGE_PLATFORM="${IMAGE_PLATFORM:-}"
IMAGE_CACHE_FROM="${IMAGE_CACHE_FROM:-}"
IMAGE_CACHE_TO="${IMAGE_CACHE_TO:-}"
CACHE_DIR="${IMAGE_CACHE_DIR:-${ROOT_DIR}/var/cache/sbx}"

if [[ "${CACHE_DIR}" != /* ]]; then
    CACHE_DIR="${ROOT_DIR}/${CACHE_DIR}"
fi

mkdir -p "${CACHE_DIR}"

cache_hash() {
    local hash_command

    if command -v shasum >/dev/null 2>&1; then
        hash_command=(shasum -a 256)
    elif command -v sha256sum >/dev/null 2>&1; then
        hash_command=(sha256sum)
    else
        cat >&2 <<'EOF'
shasum or sha256sum is required to cache the Docker sandbox image, but neither was found in PATH.
EOF
        exit 1
    fi

    {
        printf 'image-ref=%s\n' "${IMAGE_REF}"
        printf 'php-version=%s\n' "${PHP_VERSION}"
        printf 'platform=%s\n' "${IMAGE_PLATFORM:-}"
        printf 'dockerfile:\n'
        cat "${DOCKERFILE}"
        printf '\ntest-config:\n'
        cat "${TEST_CONFIG}"
    } | "${hash_command[@]}" | awk '{ print $1 }'
}

CACHE_KEY="$(cache_hash)"
CACHE_IMAGE_REF="$(printf '%s' "${IMAGE_REF}" | tr '/:' '__')"
IMAGE_TAR="${CACHE_DIR}/${CACHE_IMAGE_REF}-${CACHE_KEY}.tar"
IMAGE_TAR_TMP="${IMAGE_TAR}.tmp"
LATEST_MARKER="${CACHE_DIR}/${CACHE_IMAGE_REF}.sha256"

load_template() {
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

    printf '%s\n' "${CACHE_KEY}" > "${LATEST_MARKER}"
}

if [[ "${FORCE_REBUILD:-}" != "1" && -f "${IMAGE_TAR}" ]]; then
    printf 'Using cached sbx image tar: %s\n' "${IMAGE_TAR}"
    load_template
    exit 0
fi

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
    --file="${DOCKERFILE}" \
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
container-structure-test test --image="${IMAGE_REF}" --config="${TEST_CONFIG}"
rm -f "${IMAGE_TAR_TMP}"
docker save --output="${IMAGE_TAR_TMP}" "${IMAGE_REF}"
mv "${IMAGE_TAR_TMP}" "${IMAGE_TAR}"
set +x

load_template

printf 'Saved sbx image tar: %s\n' "${IMAGE_TAR}"
