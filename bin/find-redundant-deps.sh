#!/bin/bash
# Find redundant Container dependencies by testing each removal

cd "$(dirname "$0")/.."

PHPUNIT="vendor/phpunit/phpunit/phpunit --no-progress --stop-on-error --stop-on-failure"

# Extract all class keys from Container.php
DEPS=$(grep -oP '\w+::class(?= =>)' src/Container/Container.php | sed 's/::class//')

REDUNDANT=()
REQUIRED=()

for dep in $DEPS; do
    echo -n "Testing removal of $dep... "

    # Fast check: ContainerTest first
    if ! (REMOVE_DEP="$dep" $PHPUNIT tests/phpunit/Container/ContainerTest.php >/dev/null 2>&1); then
        echo "required (ContainerTest)"
        REQUIRED+=("$dep")
        continue
    fi

    # Full check: all unit tests
    if ! (REMOVE_DEP="$dep" $PHPUNIT --exclude-group e2e --exclude-group benchmark --exclude-group integration >/dev/null 2>&1); then
        echo "required (full suite)"
        REQUIRED+=("$dep")
        continue
    fi

    echo "REDUNDANT"
    REDUNDANT+=("$dep")
done

echo ""
echo "=== SUMMARY ==="
echo "Redundant dependencies (can be removed):"
for dep in "${REDUNDANT[@]}"; do
    echo "  - $dep"
done

echo ""
echo "Required dependencies: ${#REQUIRED[@]}"
echo "Redundant dependencies: ${#REDUNDANT[@]}"
