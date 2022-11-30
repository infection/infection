<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Humbug\PhpScoper\PhpParser\NodeVisitor\AttributeAppender;

use _HumbugBoxb47773b41c19\PhpParser\Node;
use _HumbugBoxb47773b41c19\PhpParser\NodeVisitorAbstract;
use function array_pop;
use function count;
final class ParentNodeAppender extends NodeVisitorAbstract
{
    private const PARENT_ATTRIBUTE = 'parent';
    private array $stack;
    public static function setParent(Node $node, Node $parent) : void
    {
        $node->setAttribute(self::PARENT_ATTRIBUTE, $parent);
    }
    public static function hasParent(Node $node) : bool
    {
        return $node->hasAttribute(self::PARENT_ATTRIBUTE);
    }
    public static function getParent(Node $node) : Node
    {
        return $node->getAttribute(self::PARENT_ATTRIBUTE);
    }
    public static function findParent(Node $node) : ?Node
    {
        return $node->hasAttribute(self::PARENT_ATTRIBUTE) ? $node->getAttribute(self::PARENT_ATTRIBUTE) : null;
    }
    public function beforeTraverse(array $nodes) : ?array
    {
        $this->stack = [];
        return $nodes;
    }
    public function enterNode(Node $node) : Node
    {
        if ([] !== $this->stack) {
            self::setParent($node, $this->stack[count($this->stack) - 1]);
            if ($node instanceof Node\Stmt\Const_) {
                foreach ($node->consts as $const) {
                    self::setParent($const, $node);
                    self::setParent($const->name, $const);
                }
            }
            if ($node instanceof Node\Stmt\ClassLike) {
                $name = $node->name;
                if (null !== $name) {
                    self::setParent($name, $node);
                }
            }
        }
        $this->stack[] = $node;
        return $node;
    }
    public function leaveNode(Node $node) : Node
    {
        array_pop($this->stack);
        return $node;
    }
}
