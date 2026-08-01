<?php

declare(strict_types=1);

parse_str(implode('&', array_slice($_SERVER['argv'], 1)), $options);

if (!array_key_exists('log', $options) || !array_key_exists('stage', $options)) {
    throw new RuntimeException('The debug runtime requires log and stage options.');
}

$command = array_key_exists('command', $options)
    ? json_decode(base64_decode($options['command'], true), true, flags: JSON_THROW_ON_ERROR)
    : null;

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
            'xdebug' => extension_loaded('xdebug'),
        ],
        'xdebugMode' => ini_get('xdebug.mode'),
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
