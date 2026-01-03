#!/bin/bash

RUNS=${1:-50}
PASS=0
FAIL=0

echo "Running test $RUNS times..."

for i in $(seq 1 $RUNS); do
    if chronic vendor/phpunit/phpunit/phpunit tests/phpunit/Process/Runner/InitialTestsRunnerTest.php \
        --filter test_it_stops_the_process_execution_on_the_first_error \
        --no-progress \
        2>&1; then
        ((PASS++))
    else
        ((FAIL++))
    fi
    printf "\rRun %d/%d - Pass: %d, Fail: %d" "$i" "$RUNS" "$PASS" "$FAIL"
done

echo ""
echo "Final: Pass: $PASS/$RUNS, Fail: $FAIL/$RUNS"

if [ $FAIL -gt 0 ]; then
    exit 1
fi
