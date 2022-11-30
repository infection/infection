<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Acme;

use function call_user_func;
use function file_exists;
use function _HumbugBoxb47773b41c19\iter\toArray;
if (file_exists($autoload = __DIR__ . '/vendor/scoper-autoload.php')) {
    require_once $autoload;
} else {
    require_once __DIR__ . '/vendor/autoload.php';
}
$range = '\\iter\\range' . '';
\var_dump(toArray(call_user_func($range, 1, 5)));
