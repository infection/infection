<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\PhpParser\Visitor;

use _HumbugBox9658796bb9f0\PhpParser\Node;
use _HumbugBox9658796bb9f0\PhpParser\NodeVisitorAbstract;
final class CloneVisitor extends NodeVisitorAbstract
{
    public function enterNode(Node $node) : Node
    {
        return clone $node;
    }
}
