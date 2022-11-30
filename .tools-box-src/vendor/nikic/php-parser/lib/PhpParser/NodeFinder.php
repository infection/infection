<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\PhpParser;

use _HumbugBoxb47773b41c19\PhpParser\NodeVisitor\FindingVisitor;
use _HumbugBoxb47773b41c19\PhpParser\NodeVisitor\FirstFindingVisitor;
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
