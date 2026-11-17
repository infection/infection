#!/usr/bin/env bash

cd "$(dirname "$0")"

if [ $(php -r 'echo version_compare(PHP_VERSION, "8.4.1", "<");') ]; then
    echo "Skipping test it needs PHP 8.4.1 or higher (found $(php -r 'echo PHP_VERSION;'))"
    exit 0
fi

readonly INFECTION=../../../${1}

set -eo pipefail

SYMLINK_DIRECTORY=$(mktemp -d)
readonly SYMLINK_DIRECTORY
trap 'rm -rf "$SYMLINK_DIRECTORY"' EXIT

ln -s "$PWD/src" "$SYMLINK_DIRECTORY/src"
ln -s "$PWD/build" "$SYMLINK_DIRECTORY/source-link"

php -r '
    $configuration = json_decode(file_get_contents("infection.json"), true, flags: JSON_THROW_ON_ERROR);
    $configuration["source"]["directories"][] = $argv[1] . "/source-link/../extra-src";
    $configuration["phpUnit"]["configDir"] = getcwd() . "/build";
    $configuration["logs"]["summary"] = getcwd() . "/var/infection.log";
    $configuration["tmpDir"] = getcwd() . "/var/infection-tmp";

    echo json_encode($configuration, JSON_THROW_ON_ERROR);
' "$SYMLINK_DIRECTORY" > "$SYMLINK_DIRECTORY/infection.json"

if [ "$DRIVER" = "phpdbg" ]
then
    phpdbg -qrr -d pcov.directory="$PWD" "$INFECTION" --configuration="$SYMLINK_DIRECTORY/infection.json" --initial-tests-php-options="-d pcov.directory=$PWD"
else
    php -d pcov.directory="$PWD" "$INFECTION" --configuration="$SYMLINK_DIRECTORY/infection.json" --initial-tests-php-options="-d pcov.directory=$PWD"
fi

if [ -n "$GOLDEN" ]; then
    cp -v var/infection.log expected-output.txt
fi

diff -u --ignore-all-space expected-output.txt var/infection.log
