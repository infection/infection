#!/usr/bin/env bash

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd -P)"

if ! command -v sbx >/dev/null 2>&1; then
    printf 'sbx 0.51 or later is required to test the sandbox kits.\n' >&2
    exit 1
fi

kit_options=()
test_scripts=()

for kit_dir in "${SCRIPT_DIR}"/kits/*; do
    kit_name="${kit_dir##*/}"
    # Personal hooks are outside the shared kit contract.
    if [[ "$kit_name" == project-local || ! -f "${kit_dir}/${kit_name}.yaml" ]]; then
        continue
    fi
    if [[ ! -f "${kit_dir}/test.sh" ]]; then
        printf 'Missing kit checks: %s/test.sh\n' "$kit_dir" >&2
        exit 1
    fi
    kit_options+=("--kit=${kit_dir}")
    test_scripts+=("${kit_dir}/test.sh")
done

if [[ "${#test_scripts[@]}" == 0 ]]; then
    printf 'No shared kit checks found.\n' >&2
    exit 1
fi

# mktemp reserves a unique name; never reuse or remove a user's sandbox.
name_reservation="$(mktemp -d "${TMPDIR:-/tmp}/infection-kit-test.XXXXXXXX")"
sandbox_name="${name_reservation##*/}"

cleanup() {
    result=$?
    trap - EXIT
    if ! sbx --no-input rm --force "$sandbox_name"; then
        printf 'Could not remove test sandbox %s.\n' "$sandbox_name" >&2
        if [[ "$result" == 0 ]]; then
            result=1
        fi
    fi
    rmdir "$name_reservation"
    exit "$result"
}

trap cleanup EXIT
trap 'exit 130' INT
trap 'exit 143' TERM

# No workspace or shared host skills: exercise only the composed shared kits.
sbx --no-input create shell --name "$sandbox_name" --skills=off "${kit_options[@]}"
for test_script in "${test_scripts[@]}"; do
    printf 'Testing %s\n' "$(basename "$(dirname "$test_script")")"
    sbx --no-input exec -i --user 1000 "$sandbox_name" bash -se < "$test_script"
done
