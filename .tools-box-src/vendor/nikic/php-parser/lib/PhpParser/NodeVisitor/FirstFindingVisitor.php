<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\PhpParser\NodeVisitor;

use _HumbugBoxb47773b41c19\PhpParser\Node;
use _HumbugBoxb47773b41c19\PhpParser\NodeTraverser;
use _HumbugBoxb47773b41c19\PhpParser\NodeVisitorAbstract;
class FirstFindingVisitor extends NodeVisitorAbstract
{
    protected $filterCallback;
    protected $foundNode;
    public function __construct(callable $filterCallback)
    {
        $this->filterCallback = $filterCallback;
    }
    public function getFoundNode()
    {
        return $this->foundNode;
    }
    public function beforeTraverse(array $nodes)
    {
        $this->foundNode = null;
        return null;
    }
    public function enterNode(Node $node)
    {
        $filterCallback = $this->filterCallback;
        if ($filterCallback($node)) {
            $this->foundNode = $node;
            return NodeTraverser::STOP_TRAVERSAL;
        }
        return null;
    }
}
