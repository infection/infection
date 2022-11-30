<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\PhpParser\NodeVisitor;

use _HumbugBox9658796bb9f0\PhpParser\Node;
use _HumbugBox9658796bb9f0\PhpParser\NodeVisitorAbstract;
class FindingVisitor extends NodeVisitorAbstract
{
    protected $filterCallback;
    protected $foundNodes;
    public function __construct(callable $filterCallback)
    {
        $this->filterCallback = $filterCallback;
    }
    public function getFoundNodes() : array
    {
        return $this->foundNodes;
    }
    public function beforeTraverse(array $nodes)
    {
        $this->foundNodes = [];
        return null;
    }
    public function enterNode(Node $node)
    {
        $filterCallback = $this->filterCallback;
        if ($filterCallback($node)) {
            $this->foundNodes[] = $node;
        }
        return null;
    }
}
