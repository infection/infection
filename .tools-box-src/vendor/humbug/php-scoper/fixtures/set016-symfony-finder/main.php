<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19;

use _HumbugBoxb47773b41c19\Symfony\Component\Finder\Finder;
use _HumbugBoxb47773b41c19\Symfony\Component\Finder\SplFileInfo;
require_once __DIR__ . '/vendor/autoload.php';
$finder = Finder::create()->files()->in(__DIR__)->depth(0)->sortByName();
foreach ($finder as $fileInfo) {
    echo $fileInfo->getFilename() . \PHP_EOL;
}
