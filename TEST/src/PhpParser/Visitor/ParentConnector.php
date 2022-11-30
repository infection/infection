<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\PhpParser\Visitor;

use _HumbugBox9658796bb9f0\Infection\CannotBeInstantiated;
use _HumbugBox9658796bb9f0\PhpParser\Node;
use _HumbugBox9658796bb9f0\Webmozart\Assert\Assert;
final class ParentConnector
{
    use CannotBeInstantiated;
    private const PARENT_ATTRIBUTE = 'parent';
    public static function setParent(Node $node, ?Node $parent) : void
    {
        $node->setAttribute(self::PARENT_ATTRIBUTE, $parent);
    }
    /**
    @psalm-mutation-free
    */
    public static function getParent(Node $node) : Node
    {
        Assert::true($node->hasAttribute(self::PARENT_ATTRIBUTE));
        return $node->getAttribute(self::PARENT_ATTRIBUTE);
    }
    public static function findParent(Node $node) : ?Node
    {
        return $node->getAttribute(self::PARENT_ATTRIBUTE, null);
    }
}
