#!/usr/bin/env bash

cd "$(dirname "$0")"

if [ $(php -r 'echo version_compare(PHP_VERSION, "8.3.0", "<");') ]; then
    echo "Skipping test it needs PHP 8.3.0 or higher (found $(php -r 'echo PHP_VERSION;'))"
    exit 0
fi

readonly INFECTION=../../../${1}

set -euo pipefail

assert_contains() {
    local expected="$1"
    local file="$2"

    if ! grep -F --quiet "$expected" "$file"; then
        echo "Expected $file to contain: $expected"
        exit 1
    fi
}

assert_not_contains() {
    local unexpected="$1"
    local file="$2"

    if grep -F --quiet "$unexpected" "$file"; then
        echo "Expected $file to not contain: $unexpected"
        exit 1
    fi
}

assert_line_count() {
    local expected_count="$1"
    local expected="$2"
    local file="$3"
    local actual_count

    actual_count="$(grep -F --count "$expected" "$file" || true)"

    if [ "$actual_count" != "$expected_count" ]; then
        echo "Expected $file to contain $expected_count line(s) matching: $expected"
        echo "Found: $actual_count"
        exit 1
    fi
}

php $INFECTION --no-interaction --no-progress \
    1> var/execution-no-env-variable.stdout \
    2> var/execution-no-env-variable.stderr

diff -u --ignore-all-space expected.stderr var/execution-no-env-variable.stderr
assert_contains "2 mutations were generated:" var/execution-no-env-variable.stdout
assert_contains "2 mutants were killed by Test Framework" var/execution-no-env-variable.stdout
assert_contains "Mutation Code Coverage: 100%" var/execution-no-env-variable.stdout
assert_contains "Covered Code MSI: 100%" var/execution-no-env-variable.stdout
assert_not_contains '"name": "infection.' var/execution-no-env-variable.stdout

OTEL_TRACES_EXPORTER=console php $INFECTION --no-interaction --quiet --no-progress \
    1> var/execution-with-trace-exporter.stdout \
    2> var/execution-with-trace-exporter.stderr

diff -u --ignore-all-space expected.stderr var/execution-with-trace-exporter.stderr
assert_line_count 1 '"name": "infection.run"' var/execution-with-trace-exporter.stdout
assert_line_count 1 '"name": "infection.initial_tests"' var/execution-with-trace-exporter.stdout
assert_line_count 1 '"name": "infection.mutation_generation"' var/execution-with-trace-exporter.stdout
assert_line_count 1 '"name": "infection.mutation_testing"' var/execution-with-trace-exporter.stdout
assert_line_count 2 '"name": "infection.mutation_evaluation"' var/execution-with-trace-exporter.stdout
assert_contains '"service.name": "infection"' var/execution-with-trace-exporter.stdout
assert_contains '"infection.source_file.count": 1' var/execution-with-trace-exporter.stdout
assert_contains '"infection.mutation.count":' var/execution-with-trace-exporter.stdout
assert_contains '"infection.mutation.status": "killed by tests"' var/execution-with-trace-exporter.stdout
