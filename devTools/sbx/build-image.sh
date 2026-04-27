#!/usr/bin/env bash

set -euo pipefail

. "$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd -P)/config.sh"

mkdir -p "${CACHE_DIR}"

tar_matches_cache() {
    [[ -f "${IMAGE_TAR}" && -f "${STAMP}" && "$(cat "${STAMP}")" == "${CACHE_KEY}" ]]
}

if [[ "${FORCE_REBUILD:-}" != "1" ]] && tar_matches_cache; then
    printf 'Using cached sbx image tar: %s\n' "${IMAGE_TAR}"
    exit 0
fi

save_image() {
    local container_structure_test_options

    container_structure_test_options=()

    if [[ -n "${IMAGE_PLATFORM:-}" ]]; then
        container_structure_test_options+=(
            --platform="${IMAGE_PLATFORM}"
        )
    fi

    set -x
    container-structure-test test "${container_structure_test_options[@]}" --image="${IMAGE_REF}" --config="${TEST_CONFIG}"
    rm -f "${IMAGE_TAR_TMP}"
    docker save --output="${IMAGE_TAR_TMP}" "${IMAGE_REF}"
    mv "${IMAGE_TAR_TMP}" "${IMAGE_TAR}"
    set +x

    printf '%s\n' "${CACHE_KEY}" > "${STAMP}"
    printf 'Saved sbx image tar: %s\n' "${IMAGE_TAR}"
}

image_matches_cache() {
    local image_cache_key

    image_cache_key="$(docker image inspect \
        --format='{{ index .Config.Labels "org.infection.sbx.cache-key" }}' \
        "${IMAGE_REF}" 2>/dev/null || true)"

    [[ "${image_cache_key}" == "${CACHE_KEY}" ]]
}

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

if ! command -v container-structure-test >/dev/null 2>&1; then
    cat >&2 <<'EOF'
container-structure-test is required to verify the Docker sandbox image, but it was not found in PATH.

https://github.com/GoogleContainerTools/container-structure-test
EOF
    exit 1
fi

if [[ "${FORCE_REBUILD:-}" != "1" ]] && image_matches_cache; then
    printf 'Using cached sbx image: %s\n' "${IMAGE_REF}"
    save_image
    exit 0
fi

set -x
"${DOCKER_BUILD_COMMAND[@]}" \
    "${DOCKER_BUILD_CACHE_OPTIONS[@]}" \
    --build-arg="PHP_VERSION=${PHP_VERSION}" \
    --file="${DOCKERFILE}" \
    --label="org.infection.sbx.cache-key=${CACHE_KEY}" \
    --tag="${IMAGE_REF}" \
    "${SCRIPT_DIR}"
set +x

printf '%s\n' "${IMAGE_REF}"

save_image
