<?php

declare(strict_types=1);

// Preflight: this fixture covers a memory-limit regression that only happens
// after Composer XdebugHandler restarts PHP with a loaded temporary php.ini.

require __DIR__ . '/../../../vendor/autoload.php';

$handler = new Composer\XdebugHandler\XdebugHandler('INFECTION_E2E_PHPSTAN');
$handler->setPersistent()->check();

if (Composer\XdebugHandler\XdebugHandler::getSkippedVersion() === '') {
    fwrite(STDERR, "Composer XdebugHandler did not expose a skipped Xdebug version.\n");

    exit(1);
}

if (ini_get('memory_limit') !== '-1') {
    fwrite(
        STDERR,
        sprintf(
            "Expected restarted PHP to have memory_limit=-1, got %s.\n",
            ini_get('memory_limit'),
        ),
    );

    exit(1);
}

$ini = php_ini_loaded_file();

if ($ini === false || $ini === '') {
    fwrite(STDERR, "Expected restarted PHP to have a loaded php.ini file.\n");

    exit(1);
}
