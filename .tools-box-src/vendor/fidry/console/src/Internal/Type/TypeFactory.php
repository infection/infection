<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Fidry\Console\Internal\Type;

use function array_reverse;
final class TypeFactory
{
    private function __construct()
    {
    }
    public static function createTypeFromClassNames(array $typeClassNames) : InputType
    {
        $args = [];
        foreach (array_reverse($typeClassNames) as $typeClassName) {
            $type = new $typeClassName(...$args);
            $args = [$type];
        }
        return $type;
    }
}
