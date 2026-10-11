<?php

declare(strict_types=1);

$isMutantProcess = getenv('TEST_TOKEN') !== false;

if (!$isMutantProcess) {
    return;
}

$iniPath = php_ini_loaded_file();

if ($iniPath !== false && $iniPath !== '') {
    $iniContents = file_get_contents($iniPath);

    if ($iniContents === false|| preg_match('/^memory_limit\s*=\s*32M$/m', $iniContents) === 1) {
        fwrite(STDERR, "Mutant PHPStan must not inherit PHPUnit's derived memory limit.\n");

        exit(255);
    }
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
