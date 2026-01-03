#!/bin/bash

RUNS=${1:-50}
PASS=0
FAIL=0

echo "Running test $RUNS times..."

for i in $(seq 1 $RUNS); do
    OUTPUT=$(vendor/phpunit/phpunit/phpunit tests/phpunit/Process/Runner/InitialTestsRunnerTest.php \
        --filter test_it_stops_the_process_execution_on_the_first_error \
        --no-progress \
        2>&1)
    if echo "$OUTPUT" | grep -q "^OK"; then
        ((PASS++))
    else
        ((FAIL++))
        echo ""
        echo "=== FAILURE #$FAIL at run $i ==="
        echo "$OUTPUT" | grep -A20 "Failed asserting"
        echo "==="
    fi
    printf "\rRun %d/%d - Pass: %d, Fail: %d" "$i" "$RUNS" "$PASS" "$FAIL"
done

echo ""
echo "Final: Pass: $PASS/$RUNS, Fail: $FAIL/$RUNS"

if [ $FAIL -gt 0 ]; then
    exit 1
fi
