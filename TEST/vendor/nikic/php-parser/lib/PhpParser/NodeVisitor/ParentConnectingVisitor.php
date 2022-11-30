<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\PhpParser\NodeVisitor;

use function array_pop;
use function count;
use _HumbugBox9658796bb9f0\PhpParser\Node;
use _HumbugBox9658796bb9f0\PhpParser\NodeVisitorAbstract;
final class ParentConnectingVisitor extends NodeVisitorAbstract
{
    private $stack = [];
    public function beforeTraverse(array $nodes)
    {
        $this->stack = [];
    }
    public function enterNode(Node $node)
    {
        if (!empty($this->stack)) {
            $node->setAttribute('parent', $this->stack[count($this->stack) - 1]);
        }
        $this->stack[] = $node;
    }
    public function leaveNode(Node $node)
    {
        array_pop($this->stack);
    }
}
