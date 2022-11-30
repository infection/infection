<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\PhpParser;

use _HumbugBox9658796bb9f0\PhpParser\NodeVisitor\FindingVisitor;
use _HumbugBox9658796bb9f0\PhpParser\NodeVisitor\FirstFindingVisitor;
class NodeFinder
{
    public function find($nodes, callable $filter) : array
    {
        if (!\is_array($nodes)) {
            $nodes = [$nodes];
        }
        $visitor = new FindingVisitor($filter);
        $traverser = new NodeTraverser();
        $traverser->addVisitor($visitor);
        $traverser->traverse($nodes);
        return $visitor->getFoundNodes();
    }
    public function findInstanceOf($nodes, string $class) : array
    {
        return $this->find($nodes, function ($node) use($class) {
            return $node instanceof $class;
        });
    }
    public function findFirst($nodes, callable $filter)
    {
        if (!\is_array($nodes)) {
            $nodes = [$nodes];
        }
        $visitor = new FirstFindingVisitor($filter);
        $traverser = new NodeTraverser();
        $traverser->addVisitor($visitor);
        $traverser->traverse($nodes);
        return $visitor->getFoundNode();
    }
    public function findFirstInstanceOf($nodes, string $class)
    {
        return $this->findFirst($nodes, function ($node) use($class) {
            return $node instanceof $class;
        });
    }
}
