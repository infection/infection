<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19;

use _HumbugBoxb47773b41c19\Rector\Config\RectorConfig;
use _HumbugBoxb47773b41c19\Rector\Set\ValueObject\LevelSetList;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->paths([__DIR__ . '/bin/check-composer-root-version.php', __DIR__ . '/bin/dump-composer-root-version.php', __DIR__ . '/bin/php-scoper', __DIR__ . '/bin/root-version.php', __DIR__ . '/src', __DIR__ . '/tests']);
    $rectorConfig->autoloadPaths([__DIR__ . '/vendor/autoload.php', __DIR__ . '/vendor-bin/rector/vendor/autoload.php']);
    $rectorConfig->importNames();
    $rectorConfig->sets([LevelSetList::UP_TO_PHP_81]);
    $rectorConfig->skip(['NullToStrictStringFuncCallArgRector']);
};
