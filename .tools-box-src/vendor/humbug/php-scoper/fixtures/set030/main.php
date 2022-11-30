<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19;

$autoload = __DIR__ . '/vendor/scoper-autoload.php';
if (\false === \file_exists($autoload)) {
    $autoload = __DIR__ . '/vendor/autoload.php';
}
require_once $autoload;
echo foo() ? 'ok' : 'ko';
echo \PHP_EOL;
echo bar() ? 'ok' : 'ko';
echo \PHP_EOL;
