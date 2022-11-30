<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19;

$autoload = __DIR__ . '/../vendor/scoper-autoload.php';
if (\false === \file_exists($autoload)) {
    $autoload = __DIR__ . '/../vendor/autoload.php';
}
require_once $autoload;
use _HumbugBoxb47773b41c19\Set011\DirectionaryLocator;
use _HumbugBoxb47773b41c19\Set011\Greeter;
use _HumbugBoxb47773b41c19\Set011\Dictionary;
$dir = \Phar::running(\false);
if ('' === $dir) {
    $dir = __DIR__ . \DIRECTORY_SEPARATOR . 'bin';
}
$testDir = \dirname($dir) . '/../tests';
$dictionaries = DirectionaryLocator::locateDictionaries($testDir);
$words = \array_reduce($dictionaries, function (array $words, Dictionary $dictionary) : array {
    $words = \array_merge($words, $dictionary->provideWords());
    return $words;
}, []);
$greeter = new Greeter($words);
foreach ($greeter->greet() as $greeting) {
    echo $greeting . \PHP_EOL;
}
