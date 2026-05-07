#!/usr/bin/env bash

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd -P)"
ROOT_DIR="$(cd "${SCRIPT_DIR}/../.." && pwd -P)"
DOCKERFILE="${SCRIPT_DIR}/Dockerfile"
TEST_CONFIG="${SCRIPT_DIR}/test.yaml"
PHP_VERSION="${PHP_VERSION:-8.4}"
CONTAINER_STRUCTURE_TEST_VERSION="${CONTAINER_STRUCTURE_TEST_VERSION:-1.22.1}"
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
        printf 'container-structure-test-version=%s\n' "${CONTAINER_STRUCTURE_TEST_VERSION}"
        printf 'platform=%s\n' "${IMAGE_PLATFORM:-}"
        printf 'dockerfile:\n'
        cat "${DOCKERFILE}"
        printf '\ntest-config:\n'
        cat "${TEST_CONFIG}"
    } | "${hash_command[@]}" | awk '{ print $1 }'
}

CACHE_KEY="$(cache_hash)"
CACHE_IMAGE_REF="$(printf '%s' "${IMAGE_REF}" | tr '/:' '__')"
IMAGE_TAR="${CACHE_DIR}/${CACHE_IMAGE_REF}.tar"
IMAGE_TAR_TMP="${IMAGE_TAR}.tmp"
STAMP="${CACHE_DIR}/${CACHE_IMAGE_REF}.sha256"
