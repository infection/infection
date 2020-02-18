#!/usr/bin/env bash

rm -rf infection.log

set -e

run () {
    local INFECTION=${1}

    if [ "$PHPDBG" = "1" ]
    then
        phpdbg -qrr $INFECTION
    else
        php $INFECTION
    fi
}

restore_composer() {
    cp composer.json.origin composer.json
}

run ../../../../bin/infection
diff -w expected-output.txt infection.log

rm -f composer.json

if run ../../../../bin/infection < /dev/null 2>&1 | grep -s 'File "./composer.json" cannot be found in the current directory'; then
    restore_composer
    exit 0;
fi

echo "Test should fail because there is no composer.json."
restore_composer
exit 1