#!/usr/bin/env bash

cd "$(dirname "$0")"

readonly INFECTION="../../../bin/infection --filter=nonExistentFile.php"
rm -f var/infection-execution.log || true

set -e pipefail

if [ "$DRIVER" = "phpdbg" ]
then
    phpdbg -qrr $INFECTION > var/infection-execution.log
else
    php $INFECTION > var/infection-execution.log
fi

# Check that expected output content is found in infection.log
# Read the entire infection.log into a single line (removing newlines within the content)
infection_content=$(tr '\n' ' ' < var/infection-execution.log)

while IFS= read -r line; do
    # Remove extra whitespace and normalize the expected line
    normalized_line=$(echo "$line" | tr -s ' ')

    # Search for the normalized line in the normalized infection content
    if ! echo "$infection_content" | tr -s ' ' | grep -qF "$normalized_line"; then
        echo "Expected content not found in var/infection-execution.log: $line"
        exit 1
    fi
done < expected-output.txt
