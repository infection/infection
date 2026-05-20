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

rm -rf var/cache-phpstan var/infection var/phpunit var/report

INFECTION_TELEMETRY=false php $INFECTION --no-interaction --no-progress \
    1> var/execution-no-env-variable.stdout \
    2> var/execution-no-env-variable.stderr

diff -u --ignore-all-space expected.stderr var/execution-no-env-variable.stderr
assert_contains "6 mutations were generated:" var/execution-no-env-variable.stdout
assert_contains "6 mutants were killed by Test Framework" var/execution-no-env-variable.stdout
assert_contains "Mutation Code Coverage: 100%" var/execution-no-env-variable.stdout
assert_contains "Covered Code MSI: 100%" var/execution-no-env-variable.stdout
assert_not_contains '"name": "infection.' var/execution-no-env-variable.stdout

rm -rf var/cache-phpstan var/infection var/phpunit var/report

INFECTION_TELEMETRY=true OTEL_TRACES_EXPORTER=console php $INFECTION --no-interaction --quiet --no-progress \
    1> var/execution-with-trace-exporter.stdout \
    2> var/execution-with-trace-exporter.stderr

diff -u --ignore-all-space expected.stderr var/execution-with-trace-exporter.stderr
assert_line_count 1 '"name": "infection.run"' var/execution-with-trace-exporter.stdout
assert_line_count 1 '"name": "infection.artefact_collection"' var/execution-with-trace-exporter.stdout
assert_line_count 1 '"name": "infection.initial_tests"' var/execution-with-trace-exporter.stdout
assert_line_count 1 '"name": "infection.initial_static_analysis"' var/execution-with-trace-exporter.stdout
assert_line_count 1 '"name": "infection.source_collection"' var/execution-with-trace-exporter.stdout
assert_line_count 1 '"name": "infection.mutation_analysis"' var/execution-with-trace-exporter.stdout
assert_line_count 2 '"name": "infection.ast_processing"' var/execution-with-trace-exporter.stdout
assert_line_count 2 '"name": "infection.ast_parsing"' var/execution-with-trace-exporter.stdout
assert_line_count 2 '"name": "infection.ast_enrichment"' var/execution-with-trace-exporter.stdout
assert_line_count 1 '"name": "infection.mutation_generation"' var/execution-with-trace-exporter.stdout
assert_line_count 1 '"name": "infection.mutation_evaluation"' var/execution-with-trace-exporter.stdout
assert_line_count 6 '"name": "infection.mutation_evaluation.mutation"' var/execution-with-trace-exporter.stdout
assert_line_count 6 '"name": "infection.mutation_evaluation.heuristic_suppression"' var/execution-with-trace-exporter.stdout
assert_line_count 18 '"name": "infection.mutation_evaluation.heuristic"' var/execution-with-trace-exporter.stdout
assert_line_count 6 '"name": "infection.mutation_evaluation.mutant_analysis"' var/execution-with-trace-exporter.stdout
assert_line_count 6 '"name": "infection.mutation_evaluation.mutant_materialisation"' var/execution-with-trace-exporter.stdout
assert_line_count 6 '"name": "infection.mutation_evaluation.mutant_evaluation"' var/execution-with-trace-exporter.stdout
assert_line_count 6 '"name": "infection.mutation_evaluation.process"' var/execution-with-trace-exporter.stdout
assert_line_count 1 '"name": "infection.reporting"' var/execution-with-trace-exporter.stdout
assert_contains '"service.name": "infection"' var/execution-with-trace-exporter.stdout
assert_contains '"infection.project.name": "infection\/infection"' var/execution-with-trace-exporter.stdout
assert_contains '"infection.test_framework.name": "phpunit"' var/execution-with-trace-exporter.stdout
assert_contains '"infection.test_framework.version":' var/execution-with-trace-exporter.stdout
assert_contains '"infection.static_analysis_tool.name": "phpstan"' var/execution-with-trace-exporter.stdout
assert_contains '"infection.static_analysis_tool.version":' var/execution-with-trace-exporter.stdout
assert_contains '"infection.source_file.count": 2' var/execution-with-trace-exporter.stdout
assert_contains '"infection.mutation.generated.count": 6' var/execution-with-trace-exporter.stdout
assert_contains '"infection.mutation.evaluated.count": 6' var/execution-with-trace-exporter.stdout
assert_contains '"infection.mutation.suppressed.count": 0' var/execution-with-trace-exporter.stdout
assert_contains '"infection.mutation.eligible.count": 6' var/execution-with-trace-exporter.stdout
assert_contains '"infection.mutation.ineligible.count": 0' var/execution-with-trace-exporter.stdout
assert_contains '"infection.mutation.tested_eligible.count": 6' var/execution-with-trace-exporter.stdout
assert_contains '"infection.mutation.covered.count": 6' var/execution-with-trace-exporter.stdout
assert_contains '"infection.mutation.tested_not_covered.count": 0' var/execution-with-trace-exporter.stdout
assert_contains '"infection.mutation.not_covered.count": 0' var/execution-with-trace-exporter.stdout
assert_contains '"infection.mutation.not_tested.count": 0' var/execution-with-trace-exporter.stdout
assert_contains '"infection.mutation.killed_by_tests.count": 6' var/execution-with-trace-exporter.stdout
assert_contains '"infection.mutation.killed_by_static_analysis.count": 0' var/execution-with-trace-exporter.stdout
assert_contains '"infection.mutation.escaped.count": 0' var/execution-with-trace-exporter.stdout
assert_contains '"infection.mutation.error.count": 0' var/execution-with-trace-exporter.stdout
assert_contains '"infection.mutation.timed_out.count": 0' var/execution-with-trace-exporter.stdout
assert_contains '"infection.mutation.skipped.count": 0' var/execution-with-trace-exporter.stdout
assert_contains '"infection.mutation.syntax_error.count": 0' var/execution-with-trace-exporter.stdout
assert_contains '"infection.mutation.ignored.count": 0' var/execution-with-trace-exporter.stdout
assert_contains '"infection.msi.value": 100' var/execution-with-trace-exporter.stdout
assert_contains '"infection.mutation.coverage_rate.value": 100' var/execution-with-trace-exporter.stdout
assert_contains '"infection.covered_msi.value": 100' var/execution-with-trace-exporter.stdout
assert_contains '"infection.msi.threshold": 0' var/execution-with-trace-exporter.stdout
assert_contains '"infection.covered_msi.threshold": 0' var/execution-with-trace-exporter.stdout
assert_line_count 6 '"infection.mutation.status": "killed by tests"' var/execution-with-trace-exporter.stdout
