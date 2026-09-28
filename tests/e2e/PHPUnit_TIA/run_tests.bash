#!/usr/bin/env bash

set -eo pipefail
cd "$(dirname "$0")"

if php -r 'exit(version_compare(PHP_VERSION, "8.4.1", "<") ? 0 : 1);'; then
    echo "Skipping PHPUnit TIA: requires PHP 8.4.1 or higher."
    exit 0
fi

readonly INFECTION=../../../${1:-bin/infection}

rm -rf var .infection
mkdir -p var/{cold,warm,project-seeded,declared-cold,declared-warm,disabled,project-reuse,relocated,invalidated}

for phase in cold warm project-seeded declared-cold declared-warm disabled; do
    extra_args=()
    if [[ "$phase" == project-seeded ]]; then
        # A plain PHPUnit run must populate data that Infection can read, despite
        # Infection changing reporting, execution order, and absolute paths.
        rm -rf .infection
        XDEBUG_MODE=coverage php vendor/bin/phpunit --configuration phpunit.xml \
            --cache-directory="$PWD/.infection/phpunit" --record-test-run-history \
            --record-test-impact-data > var/project-seeded/recording.log 2>&1
    elif [[ "$phase" == declared-* ]]; then
        extra_args=(--test-framework-extra-args=--derive-test-impact-data-from-coverage-targets)
    elif [[ "$phase" == disabled ]]; then
        extra_args=(--test-framework-extra-args=--do-not-record-test-impact-data)
    fi
    php "$INFECTION" src/Calculator.php --min-msi=100 --debug --no-progress "${extra_args[@]}" \
        > "var/$phase/console.log" 2>&1 || { cat "var/$phase/console.log"; exit 1; }

    diff -u --ignore-all-space expected-output.txt var/infection.log
    cp var/infection-tmp/infection/junit.xml "var/$phase/junit.xml"
    cp var/infection-tmp/infection/coverage-xml/Calculator.php.xml "var/$phase/coverage.xml"
    cp var/infection.json "var/$phase/mutations.json"

    if [[ "$phase" == warm ]]; then
        # Read Infection's recording using the project's original XML, then a
        # relocated generated XML. Neither invocation refreshes the recording.
        cp var/infection-tmp/infection/phpunitConfiguration.initial.infection.xml var/relocated/phpunit.xml
        for query in project-reuse relocated invalidated; do
            config=phpunit.xml
            query_args=()
            if [[ "$query" == relocated ]]; then
                config=var/relocated/phpunit.xml
                query_args=(--colors=always)
            elif [[ "$query" == invalidated ]]; then
                query_args=(-d memory_limit=513M)
            fi
            php vendor/bin/phpunit --configuration "$config" \
                --cache-directory="$PWD/.infection/phpunit" --record-test-run-history \
                --do-not-record-test-impact-data --impacted-by="$PWD/src/Calculator.php" \
                --log-junit="var/$query/junit.xml" "${query_args[@]}" \
                > "var/$query/console.log" 2>&1
        done
    fi
done

php verify.php
