<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Humbug\PhpScoper\PhpParser\NodeVisitor\Resolver;

use _HumbugBoxb47773b41c19\Humbug\PhpScoper\NotInstantiable;
use _HumbugBoxb47773b41c19\PhpParser\Node\Name;
final class OriginalNameResolver
{
    use NotInstantiable;
    private const ORIGINAL_NAME_ATTRIBUTE = 'originalName';
    public static function hasOriginalName(Name $namespace) : bool
    {
        return $namespace->hasAttribute(self::ORIGINAL_NAME_ATTRIBUTE);
    }
    public static function getOriginalName(Name $name) : Name
    {
        if (!self::hasOriginalName($name)) {
            return $name;
        }
        return $name->getAttribute(self::ORIGINAL_NAME_ATTRIBUTE);
    }
}
