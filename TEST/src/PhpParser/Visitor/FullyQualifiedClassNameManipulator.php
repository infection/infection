<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\PhpParser\Visitor;

use _HumbugBox9658796bb9f0\Infection\CannotBeInstantiated;
use _HumbugBox9658796bb9f0\PhpParser\Node;
use _HumbugBox9658796bb9f0\PhpParser\Node\Name\FullyQualified;
use _HumbugBox9658796bb9f0\Webmozart\Assert\Assert;
final class FullyQualifiedClassNameManipulator
{
    use CannotBeInstantiated;
    private const FQN_ATTRIBUTE = 'fullyQualifiedClassName';
    public static function setFqcn(Node $node, ?FullyQualified $fqcn) : void
    {
        $node->setAttribute(self::FQN_ATTRIBUTE, $fqcn);
    }
    public static function hasFqcn(Node $node) : bool
    {
        return $node->hasAttribute(self::FQN_ATTRIBUTE);
    }
    public static function getFqcn(Node $node) : ?FullyQualified
    {
        Assert::true(self::hasFqcn($node));
        return $node->getAttribute(self::FQN_ATTRIBUTE);
    }
}
