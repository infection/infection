<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Fidry\Console\Internal\Generator;

use _HumbugBoxb47773b41c19\Fidry\Console\Internal\Type\InputType;
use function array_map;
use function array_unshift;
use function implode;
final class GetterNameGenerator
{
    private function __construct()
    {
    }
    public static function generateMethodName(array $typeClassNames) : string
    {
        $typeParts = array_map(static fn(string $typeClassName) => self::normalizeTypeName($typeClassName), TypeNameSorter::sortClassNames($typeClassNames));
        array_unshift($typeParts, 'as');
        return implode('', $typeParts);
    }
    private static function normalizeTypeName(string $typeClassName) : string
    {
        return \mb_substr(ClassName::getShortClassName($typeClassName), 0, -4);
    }
}
