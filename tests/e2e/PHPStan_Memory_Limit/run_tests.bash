#!/usr/bin/env bash

cd "$(dirname "$0")"

readonly INFECTION=../../../${1}

set -e pipefail

if [ "${DRIVER:-}" != "xdebug" ]
then
    # This regression requires Composer XdebugHandler's temporary php.ini.
    # PCOV and phpdbg do not exercise that path.
    exit 0
fi

if ! XDEBUG_MODE=coverage php -r 'exit(extension_loaded("xdebug") ? 0 : 1);'
then
    echo "PHPStan_Memory_Limit requires Xdebug to be installed and loadable with XDEBUG_MODE=coverage." >&2
    exit 1
fi

restart_check="$(mktemp)"
trap 'rm -f "$restart_check"' EXIT

cat > "$restart_check" <<'PHP'
<?php

require getcwd() . '/../../../vendor/autoload.php';

$handler = new Composer\XdebugHandler\XdebugHandler('INFECTION_E2E_PHPSTAN');
$handler->setPersistent()->check();

if (Composer\XdebugHandler\XdebugHandler::getSkippedVersion() === '') {
    fwrite(STDERR, "Composer XdebugHandler did not expose a skipped Xdebug version.\n");
    exit(1);
}

if (ini_get('memory_limit') !== '-1') {
    fwrite(STDERR, sprintf(
        "Expected restarted PHP to have memory_limit=-1, got %s.\n",
        ini_get('memory_limit'),
    ));
    exit(1);
}

$ini = php_ini_loaded_file();

if ($ini === false || $ini === '') {
    fwrite(STDERR, "Expected restarted PHP to have a loaded php.ini file.\n");
    exit(1);
}

file_put_contents($ini, "\nmemory_limit = 32M\n", FILE_APPEND);

exec(PHP_BINARY . ' -r ' . escapeshellarg('echo ini_get("memory_limit");'), $output, $exitCode);

if ($exitCode !== 0 || ($output[0] ?? '') !== '32M') {
    fwrite(STDERR, "Expected child PHP processes to inherit the MemoryLimiter php.ini cap.\n");
    exit(1);
}
PHP

if ! XDEBUG_MODE=coverage php -d memory_limit=-1 "$restart_check"
then
    echo "PHPStan_Memory_Limit requires Composer XdebugHandler to restart PHP with memory_limit=-1 and a writable inherited php.ini." >&2
    exit 1
fi

XDEBUG_MODE=coverage php -d memory_limit=-1 $INFECTION --no-progress --threads=2

if [ -n "$GOLDEN" ]; then
    cp -v infection.log expected-output.txt
fi;

diff -u --ignore-all-space expected-output.txt infection.log
