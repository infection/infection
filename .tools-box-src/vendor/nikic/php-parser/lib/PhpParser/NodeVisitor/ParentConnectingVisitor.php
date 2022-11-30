<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\PhpParser\NodeVisitor;

use function array_pop;
use function count;
use _HumbugBoxb47773b41c19\PhpParser\Node;
use _HumbugBoxb47773b41c19\PhpParser\NodeVisitorAbstract;
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
