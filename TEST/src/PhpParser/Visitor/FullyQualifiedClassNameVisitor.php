<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\PhpParser\Visitor;

use _HumbugBox9658796bb9f0\PhpParser\Node;
use _HumbugBox9658796bb9f0\PhpParser\NodeVisitorAbstract;
final class FullyQualifiedClassNameVisitor extends NodeVisitorAbstract
{
    private ?Node\Name $namespace = null;
    public function enterNode(Node $node) : ?Node
    {
        if ($node instanceof Node\Stmt\Namespace_) {
            $this->namespace = $node->name;
            return null;
        }
        if ($node instanceof Node\Stmt\ClassLike) {
            FullyQualifiedClassNameManipulator::setFqcn($node, $node->name !== null ? Node\Name\FullyQualified::concat($this->namespace, $node->name->toString()) : null);
        }
        return null;
    }
}
