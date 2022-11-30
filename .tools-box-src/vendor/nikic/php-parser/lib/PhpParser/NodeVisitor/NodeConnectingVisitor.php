<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\PhpParser\NodeVisitor;

use _HumbugBoxb47773b41c19\PhpParser\Node;
use _HumbugBoxb47773b41c19\PhpParser\NodeVisitorAbstract;
final class NodeConnectingVisitor extends NodeVisitorAbstract
{
    private $stack = [];
    private $previous;
    public function beforeTraverse(array $nodes)
    {
        $this->stack = [];
        $this->previous = null;
    }
    public function enterNode(Node $node)
    {
        if (!empty($this->stack)) {
            $node->setAttribute('parent', $this->stack[\count($this->stack) - 1]);
        }
        if ($this->previous !== null && $this->previous->getAttribute('parent') === $node->getAttribute('parent')) {
            $node->setAttribute('previous', $this->previous);
            $this->previous->setAttribute('next', $node);
        }
        $this->stack[] = $node;
    }
    public function leaveNode(Node $node)
    {
        $this->previous = $node;
        \array_pop($this->stack);
    }
}
