<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Humbug\PhpScoper\PhpParser\NodeVisitor\NamespaceStmt;

use _HumbugBoxb47773b41c19\Humbug\PhpScoper\NotInstantiable;
use _HumbugBoxb47773b41c19\PhpParser\Node\Name;
use _HumbugBoxb47773b41c19\PhpParser\Node\Stmt\Namespace_;
use _HumbugBoxb47773b41c19\PhpParser\NodeVisitorAbstract;
final class NamespaceManipulator extends NodeVisitorAbstract
{
    use NotInstantiable;
    private const ORIGINAL_NAME_ATTRIBUTE = 'originalName';
    public static function hasOriginalName(Namespace_ $namespace) : bool
    {
        return $namespace->hasAttribute(self::ORIGINAL_NAME_ATTRIBUTE);
    }
    public static function getOriginalName(Namespace_ $namespace) : ?Name
    {
        if (!self::hasOriginalName($namespace)) {
            return $namespace->name;
        }
        return $namespace->getAttribute(self::ORIGINAL_NAME_ATTRIBUTE);
    }
    public static function setOriginalName(Namespace_ $namespace, ?Name $originalName) : void
    {
        $namespace->setAttribute(self::ORIGINAL_NAME_ATTRIBUTE, $originalName);
    }
}
