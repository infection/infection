<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Acme;

use Throwable;
use const PHP_EOL;
echo 'Autoload Scoped code.' . PHP_EOL;
require __DIR__ . '/scoped-guzzle5-include/index.php';
echo 'Autoload code.' . PHP_EOL;
require __DIR__ . '/vendor/autoload.php';
try {
    \_HumbugBoxb47773b41c19\GuzzleHttp\describe_type('hello');
} catch (Throwable $throwable) {
    echo $throwable->getMessage() . PHP_EOL;
}
