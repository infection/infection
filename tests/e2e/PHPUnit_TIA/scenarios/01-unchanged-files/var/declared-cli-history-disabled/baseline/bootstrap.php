<?php

declare(strict_types=1);

$loader = require __DIR__ . '/../../../../../vendor/autoload.php';
$loader->addPsr4('Infection\\E2ETests\\PHPUnitTIA\\', __DIR__ . '/src', prepend: true);

$mutant = getenv('TEST_TOKEN') !== false;
file_put_contents(
    __DIR__ . '/var/' . ($mutant ? 'mutant' : 'initial') . '-command.json',
    json_encode($_SERVER['argv'], JSON_THROW_ON_ERROR),
);

if ($mutant) {
    // Mutant bootstrap runs after initial recording and before PHPUnit can change it.
    $recording = __DIR__ . '/var/phpunit-scenario01/test-impact-data';
    file_put_contents(
        __DIR__ . '/var/impact-before-mutant.json',
        json_encode(is_file($recording) ? hash_file('sha256', $recording) : null, JSON_THROW_ON_ERROR),
    );
}
