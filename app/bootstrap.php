<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
use Infection\Php\ConfigBuilder;
use Infection\Php\XdebugHandler;

$files = [
    __DIR__ . '/../../../autoload.php',
    __DIR__ . '/../vendor/autoload.php',
    __DIR__ . '/vendor/autoload.php',
];

foreach ($files as $file) {
    if (file_exists($file)) {
        define('INFECTION_COMPOSER_INSTALL', $file);

        break;
    }
}

unset($files);

if (!defined('INFECTION_COMPOSER_INSTALL')) {
    fwrite(
        STDERR,
        'You need to set up the project dependencies using Composer:' . PHP_EOL . PHP_EOL .
        '    composer install' . PHP_EOL . PHP_EOL .
        'You can learn all about Composer on https://getcomposer.org/.' . PHP_EOL
    );

    die(1);
}

require INFECTION_COMPOSER_INSTALL;

$isDebuggerDisabled = empty((string) getenv(XdebugHandler::ENV_DISABLE_XDEBUG));

$xdebug = new XdebugHandler(new ConfigBuilder(sys_get_temp_dir()));
$xdebug->check();
unset($xdebug);

if (PHP_SAPI !== 'phpdbg' && $isDebuggerDisabled && !extension_loaded('xdebug')) {
    fwrite(
        STDERR,
        'You need to use phpdbg or install and enable xdebug in order to allow for code coverage generation.' . PHP_EOL
    );

    die(1);
}

$container = require __DIR__ . '/container.php';
