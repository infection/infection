<?php

declare(strict_types=1);

// This script represents the "debug" test framework.
//
// Its purpose is to collect information about its environment and how it was
// executed and log them in the given file.

$options = getopt('', ['log:', 'stage:', 'mutationHash:', 'command:']);

if (!array_key_exists('log', $options) || !array_key_exists('stage', $options)) {
    throw new RuntimeException('The debug runtime requires log and stage options.');
}

$command = array_key_exists('command', $options)
    ? json_decode(base64_decode($options['command'], true), true, flags: JSON_THROW_ON_ERROR)
    : null;

$xdebugLoaded = extension_loaded('xdebug');
$xdebugModes = $xdebugLoaded && function_exists('xdebug_info')
    ? xdebug_info('mode')
    : [];
$coverageDriver = match (true) {
    PHP_SAPI === 'phpdbg' => 'phpdbg',
    extension_loaded('pcov') => 'pcov',
    in_array('coverage', $xdebugModes, true) => 'xdebug',
    default => null,
};

$record = [
    'stage' => $options['stage'],
    'mutationHash' => $options['mutationHash'] ?? null,
    'command' => $command,
    'argv' => $_SERVER['argv'],
    'php' => [
        'binary' => PHP_BINARY,
        'sapi' => PHP_SAPI,
        'memoryLimit' => ini_get('memory_limit'),
        'loadedIni' => php_ini_loaded_file(),
        'scannedIniFiles' => php_ini_scanned_files(),
        'extensions' => [
            'pcov' => extension_loaded('pcov'),
            'xdebug' => $xdebugLoaded,
        ],
        'xdebugMode' => ini_get('xdebug.mode'),
    ],
    'coverage' => [
        'driver' => $coverageDriver,
        'xdebugAvailable' => function_exists('xdebug_start_code_coverage'),
        'pcovAvailable' => function_exists('pcov\\start'),
        'phpdbg' => PHP_SAPI === 'phpdbg',
    ],
    'xdebug' => [
        'loaded' => $xdebugLoaded,
        'version' => phpversion('xdebug'),
        'mode' => ini_get('xdebug.mode'),
        'coverageAvailable' => in_array('coverage', $xdebugModes, true),
    ],
    'xdebugHandler' => [
        'restartSettingsPresent' => getenv('XDEBUG_HANDLER_SETTINGS') !== false,
        'originalInisPresent' => getenv('INFECTION_ORIGINAL_INIS') !== false,
        'allowXdebug' => getenv('INFECTION_ALLOW_XDEBUG'),
        'phprc' => getenv('PHPRC'),
        'phpIniScanDir' => getenv('PHP_INI_SCAN_DIR'),
    ],
    'environment' => [
        'INFECTION' => getenv('INFECTION'),
        'TEST_TOKEN' => getenv('TEST_TOKEN'),
        'XDEBUG_MODE' => getenv('XDEBUG_MODE'),
    ],
];

file_put_contents(
    $options['log'],
    json_encode($record, JSON_THROW_ON_ERROR) . PHP_EOL,
    FILE_APPEND | LOCK_EX,
);

if ($options['stage'] === 'test-framework-initial') {
    echo "DEBUG_TEST_FRAMEWORK_PASSED\nMemory: 16.00 MB\n";
} elseif ($options['stage'] === 'test-framework-mutant') {
    echo "DEBUG_TEST_FRAMEWORK_PASSED\n";
}
