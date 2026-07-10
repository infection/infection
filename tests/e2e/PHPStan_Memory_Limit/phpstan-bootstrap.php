<?php

declare(strict_types=1);

$isMutantAnalysis = false;

foreach ($_SERVER['argv'] ?? [] as $argument) {
    if (str_starts_with((string) $argument, '--tmp-file=')) {
        $isMutantAnalysis = true;

        break;
    }
}

if (!$isMutantAnalysis) {
    return;
}

$iniPath = php_ini_loaded_file();

if ($iniPath === false || $iniPath === '') {
    fwrite(STDERR, "Mutant PHPStan is expected to run with the XdebugHandler temporary php.ini.\n");

    exit(255);
}

$iniContents = file_get_contents($iniPath);

if ($iniContents === false || preg_match('/^memory_limit\s*=\s*32M$/m', $iniContents) !== 1) {
    fwrite(STDERR, "Mutant PHPStan is expected to inherit the MemoryLimiter php.ini cap.\n");

    exit(255);
}

if (ini_get('memory_limit') !== '-1') {
    fwrite(
        STDERR,
        sprintf(
            "Mutant PHPStan is expected to run without an effective PHP memory_limit, got %s.\n",
            ini_get('memory_limit'),
        ),
    );

    exit(255);
}
