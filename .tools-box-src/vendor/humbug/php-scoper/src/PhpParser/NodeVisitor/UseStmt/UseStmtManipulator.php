<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Humbug\PhpScoper\PhpParser\NodeVisitor\UseStmt;

use _HumbugBoxb47773b41c19\Humbug\PhpScoper\NotInstantiable;
use _HumbugBoxb47773b41c19\PhpParser\Node\Name;
use _HumbugBoxb47773b41c19\PhpParser\Node\Stmt\UseUse;
use _HumbugBoxb47773b41c19\PhpParser\NodeVisitorAbstract;
final class UseStmtManipulator extends NodeVisitorAbstract
{
    use NotInstantiable;
    private const ORIGINAL_NAME_ATTRIBUTE = 'originalName';
    public static function hasOriginalName(UseUse $use) : bool
    {
        return $use->hasAttribute(self::ORIGINAL_NAME_ATTRIBUTE);
    }
    public static function getOriginalName(UseUse $use) : ?Name
    {
        if (!self::hasOriginalName($use)) {
            return $use->name;
        }
        return $use->getAttribute(self::ORIGINAL_NAME_ATTRIBUTE);
    }
    public static function setOriginalName(UseUse $use, ?Name $originalName) : void
    {
        $use->setAttribute(self::ORIGINAL_NAME_ATTRIBUTE, $originalName);
    }
}
