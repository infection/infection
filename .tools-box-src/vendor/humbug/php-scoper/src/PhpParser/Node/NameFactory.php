<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Humbug\PhpScoper\PhpParser\Node;

use _HumbugBoxb47773b41c19\Humbug\PhpScoper\NotInstantiable;
use InvalidArgumentException;
use _HumbugBoxb47773b41c19\PhpParser\Node\Name;
final class NameFactory
{
    use NotInstantiable;
    public static function concat($name1, $name2, array $attributes = []) : Name
    {
        if (null === $name1 && null === $name2) {
            throw new InvalidArgumentException('Expected one of the names to not be null');
        }
        return Name::concat($name1, $name2, $attributes);
    }
}
