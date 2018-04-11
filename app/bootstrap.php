<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
$autoloaderInWorkingDirectory = getcwd() . '/vendor/autoload.php';
if (is_file($autoloaderInWorkingDirectory)) {
    require_once $autoloaderInWorkingDirectory;
}
unset($autoloaderInWorkingDirectory);

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

$container = new \Infection\Console\InfectionContainer();
