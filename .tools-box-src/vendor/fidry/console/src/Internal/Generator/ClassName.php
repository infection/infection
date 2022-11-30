<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Fidry\Console\Internal\Generator;

use function mb_strrpos;
final class ClassName
{
    private function __construct()
    {
    }
    public static function getShortClassName(string $className) : string
    {
        if (\false !== ($pos = mb_strrpos($className, '\\'))) {
            return \mb_substr($className, $pos + 1);
        }
        return $className;
    }
}
