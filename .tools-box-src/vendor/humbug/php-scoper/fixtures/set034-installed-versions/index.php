<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Acme;

use _HumbugBoxb47773b41c19\Composer\InstalledVersions;
use function file_exists;
use const PHP_EOL;
require file_exists(__DIR__ . '/vendor/scoper-autoload.php') ? __DIR__ . '/vendor/scoper-autoload.php' : __DIR__ . '/vendor/autoload.php';
if (InstalledVersions::isInstalled('nikic/iter')) {
    echo "ok." . PHP_EOL;
}
