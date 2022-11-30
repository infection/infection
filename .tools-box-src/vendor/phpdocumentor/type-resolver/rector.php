<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19;

use _HumbugBoxb47773b41c19\Rector\CodeQuality\Rector\Class_\InlineConstructorDefaultToPropertyRector;
use _HumbugBoxb47773b41c19\Rector\Config\RectorConfig;
use _HumbugBoxb47773b41c19\Rector\Set\ValueObject\LevelSetList;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->paths([__DIR__ . '/src', __DIR__ . '/tests/unit']);
    $rectorConfig->rule(InlineConstructorDefaultToPropertyRector::class);
    $rectorConfig->rule(Rector\CodeQuality\Rector\Class_\CompleteDynamicPropertiesRector::class);
    $rectorConfig->rule(Rector\TypeDeclaration\Rector\Closure\AddClosureReturnTypeRector::class);
    $rectorConfig->rule(Rector\PHPUnit\Rector\Class_\AddProphecyTraitRector::class);
    $rectorConfig->importNames();
    $rectorConfig->sets([LevelSetList::UP_TO_PHP_74]);
};
