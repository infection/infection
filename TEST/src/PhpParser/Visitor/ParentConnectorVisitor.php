<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\PhpParser\Visitor;

use function array_pop;
use function count;
use _HumbugBox9658796bb9f0\PhpParser\Node;
use _HumbugBox9658796bb9f0\PhpParser\NodeVisitor;
final class ParentConnectorVisitor implements NodeVisitor
{
    private array $stack = [];
    public function beforeTraverse(array $nodes) : ?array
    {
        $this->stack = [];
        return null;
    }
    public function afterTraverse(array $nodes) : ?array
    {
        return null;
    }
    public function enterNode(Node $node)
    {
        $stackCount = count($this->stack);
        ParentConnector::setParent($node, $this->stack[$stackCount - 1] ?? null);
        $this->stack[] = $node;
        return null;
    }
    public function leaveNode(Node $node)
    {
        array_pop($this->stack);
        return null;
    }
}
