<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19;

require_once __DIR__ . '/../vendor/autoload.php';
use _HumbugBoxb47773b41c19\Set015\Greeter;
$c = new Pimple\Container(['hello' => 'Hello world!']);
echo (new Greeter())->greet($c) . \PHP_EOL;
