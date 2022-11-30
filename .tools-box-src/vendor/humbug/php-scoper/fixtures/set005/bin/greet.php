<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19;

require_once __DIR__ . '/../vendor/autoload.php';
use _HumbugBoxb47773b41c19\Assert\Assertion;
use _HumbugBoxb47773b41c19\Set005\Greeter;
Assertion::true(\true);
echo (new Greeter())->greet() . \PHP_EOL;
